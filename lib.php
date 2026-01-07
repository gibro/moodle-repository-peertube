<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * PeerTube repository plugin
 *
 * @package    repository_peertube
 * @copyright  2010 Dongsheng Cai {@link http://dongsheng.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * repository_peertube class
 *
 * @package    repository_peertube
 * @copyright  2009 Dongsheng Cai {@link http://dongsheng.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_peertube extends repository {
    /** @var int maximum number of thumbs per page */
    const PEERTUBE_THUMBS_PER_PAGE = 27;

    /** @var string Access token for PeerTube API */
    private $accesstoken;

    /** @var string PeerTube instance URL */
    private $instanceurl;

    /** @var string Search keyword */
    protected $keyword;

    /**
     * Constructor
     *
     * @param int $repositoryid
     * @param object $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);

        $this->accesstoken = $this->get_option('accesstoken');
        $this->instanceurl = $this->get_option('instanceurl');

        if (empty($this->instanceurl) || empty($this->accesstoken)) {
            $this->disabled = true;
        }
    }

    /**
     * Save configuration options
     *
     * @param array $options
     * @return boolean
     */
    public function set_option($options = array()) {
        if (!empty($options['accesstoken'])) {
            // Sanitize access token
            $token = trim($options['accesstoken']);
            set_config('accesstoken', $token, 'peertube');
        }
        if (!empty($options['instanceurl'])) {
            // Validate and sanitize URL
            $url = trim($options['instanceurl']);
            $url = rtrim($url, '/');
            // Basic URL validation
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                set_config('instanceurl', $url, 'peertube');
            }
        }
        unset($options['accesstoken'], $options['instanceurl']);
        return parent::set_option($options);
    }

    /**
     * Get configuration option
     *
     * @param string $config
     * @return mixed
     */
    public function get_option($config = '') {
        if ($config === 'accesstoken') {
            return trim(get_config('peertube', 'accesstoken'));
        } else if ($config === 'instanceurl') {
            return trim(get_config('peertube', 'instanceurl'));
        } else if (empty($config)) {
            $options = array();
            $options['accesstoken'] = trim(get_config('peertube', 'accesstoken'));
            $options['instanceurl'] = trim(get_config('peertube', 'instanceurl'));
            return $options;
        }
        return parent::get_option($config);
    }

    /**
     * Check if user is logged in (has searched)
     *
     * @return bool
     */
    public function check_login() {
        return !empty($this->keyword);
    }

    /**
     * Return search results
     *
     * @param string $search_text
     * @param int $page
     * @return array
     */
    public function search($search_text, $page = 0) {
        global $SESSION;

        $sort = optional_param('peertube_sort', '', PARAM_TEXT);
        $sess_keyword = 'peertube_'.$this->id.'_keyword';
        $sess_sort = 'peertube_'.$this->id.'_sort';

        // Retrieve cached keyword and sort for pagination
        if ($page && !$search_text && isset($SESSION->{$sess_keyword})) {
            $search_text = $SESSION->{$sess_keyword};
        }
        if ($page && !$sort && isset($SESSION->{$sess_sort})) {
            $sort = $SESSION->{$sess_sort};
        }
        if (!$sort) {
            $sort = '-publishedAt';
        }

        // Save search in session
        $SESSION->{$sess_keyword} = $search_text;
        $SESSION->{$sess_sort} = $sort;

        $this->keyword = $search_text;
        $ret = array();
        $ret['nologin'] = true;
        $ret['page'] = max(1, (int)$page);
        $start = ($ret['page'] - 1) * self::PEERTUBE_THUMBS_PER_PAGE;
        $max = self::PEERTUBE_THUMBS_PER_PAGE;

        // Get search keyword from session if empty
        if (empty($search_text) && isset($SESSION->{$sess_keyword})) {
            $search_text = $SESSION->{$sess_keyword};
        }
        $search_keyword = !empty($search_text) ? trim($search_text) : '';

        $result = $this->_get_collection($search_keyword, $start, $max, $sort);
        $ret['list'] = $result['list'];
        $ret['norefresh'] = true;
        $ret['nosearch'] = false;
        $ret['dynload'] = true;

        // Determine pagination
        $total = isset($result['total']) ? (int)$result['total'] : 0;
        $currentcount = count($ret['list']);

        if ($currentcount == 0) {
            $ret['pages'] = $ret['page'];
        } else if ($total > 0) {
            $itemsSoFar = $start + $currentcount;
            $ret['pages'] = ($itemsSoFar >= $total) ? $ret['page'] : -1;
        } else {
            $ret['pages'] = -1;
        }

        return $ret;
    }

    /**
     * Get video collection from PeerTube API
     *
     * @param string $keyword Search keyword
     * @param int $start Start offset
     * @param int $max Maximum results
     * @param string $sort Sort order
     * @return array with 'list' and 'total' keys
     * @throws moodle_exception
     */
    private function _get_collection($keyword, $start, $max, $sort) {
        if (empty($keyword)) {
            return array('list' => array(), 'total' => 0);
        }

        // Validate instance URL
        if (empty($this->instanceurl) || !filter_var($this->instanceurl, FILTER_VALIDATE_URL)) {
            throw new moodle_exception('apierror', 'repository_peertube', '', 'Invalid instance URL');
        }

        // Sanitize inputs
        $trimmed_keyword = trim($keyword);
        $start = max(0, (int)$start);
        $max = max(1, min(100, (int)$max)); // Limit between 1 and 100
        $sort = clean_param($sort, PARAM_ALPHAEXT); // Allow alphanumeric and dashes

        $apiurl = $this->instanceurl . '/api/v1/users/me/videos';

        $params = array(
            'start' => $start,
            'count' => $max,
            'sort' => $sort,
            'search' => $trimmed_keyword
        );

        $url = $apiurl . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        // Make API request
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->accesstoken,
                'Content-Type: application/json'
            ),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlerror = curl_error($curl);
        curl_close($curl);

        if ($response === false || !empty($curlerror)) {
            throw new moodle_exception('apierror', 'repository_peertube', '', 'CURL error: ' . $curlerror);
        }

        if ($httpcode !== 200) {
            // Don't expose URL or access token in error messages for security
            throw new moodle_exception('apierror', 'repository_peertube', '', 'HTTP error: ' . $httpcode);
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new moodle_exception('apierror', 'repository_peertube', '', 'JSON decode error: ' . json_last_error_msg());
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new moodle_exception('apierror', 'repository_peertube', '', 'Invalid API response format');
        }

        // Get total count
        $total = 0;
        if (isset($data['total'])) {
            $total = (int)$data['total'];
        } else if (isset($data['totalCount'])) {
            $total = (int)$data['totalCount'];
        }

        $list = array();

        foreach ($data['data'] as $video) {
            // Sanitize video data
            $title = isset($video['name']) ? clean_param($video['name'], PARAM_TEXT) : '';
            $description = isset($video['description']) ? clean_param($video['description'], PARAM_TEXT) : '';
            $uuid = isset($video['uuid']) ? clean_param($video['uuid'], PARAM_ALPHANUMEXT) : '';

            // Get thumbnail URL
            $thumbnail = '';
            if (isset($video['thumbnailPath'])) {
                $thumbpath = clean_param($video['thumbnailPath'], PARAM_PATH);
                $thumbnail = $this->instanceurl . $thumbpath;
            } else if (isset($video['thumbnailUrl'])) {
                $thumbnail = clean_param($video['thumbnailUrl'], PARAM_URL);
            }

            // Get embed URL - construct from UUID for security
            $embedurl = '';
            if (!empty($uuid)) {
                // Construct embed URL from validated UUID
                $embedurl = $this->instanceurl . '/videos/embed/' . $uuid;
            } else if (isset($video['embedUrl']) && !empty($video['embedUrl'])) {
                // Fallback to API embed URL if UUID not available
                $embedurl = clean_param($video['embedUrl'], PARAM_URL);
            }

            if (empty($embedurl)) {
                continue;
            }

            // Format date
            $date = '';
            if (isset($video['publishedAt']) && !empty($video['publishedAt'])) {
                $timestamp = strtotime($video['publishedAt']);
                $date = ($timestamp !== false) ? $timestamp : 0;
            }

            $list[] = array(
                'shorttitle' => $title,
                'thumbnail_title' => $description,
                'title' => $title . '.avi',
                'thumbnail' => $thumbnail,
                'thumbnail_width' => 320,
                'thumbnail_height' => 180,
                'size' => '',
                'date' => $date,
                'source' => $embedurl . '#' . $title,
            );
        }

        return array('list' => $list, 'total' => $total);
    }

    /**
     * Global search not supported
     *
     * @return bool
     */
    public function global_search() {
        return false;
    }

    /**
     * Get listing (empty - requires search)
     *
     * @param string $path
     * @param string $page
     * @return array
     */
    public function get_listing($path = '', $page = '') {
        return array();
    }

    /**
     * Generate search form
     *
     * @param bool $ajax
     * @return array
     */
    public function print_login($ajax = true) {
        $ret = array();
        $search = new stdClass();
        $search->type = 'text';
        $search->id = 'peertube_search';
        $search->name = 's';
        $search->label = get_string('search', 'repository_peertube') . ': ';

        $sort = new stdClass();
        $sort->type = 'select';
        $sort->options = array(
            (object)array('value' => '-publishedAt', 'label' => get_string('sortpublished', 'repository_peertube')),
            (object)array('value' => 'publishedAt', 'label' => get_string('sortpublishedasc', 'repository_peertube')),
            (object)array('value' => '-views', 'label' => get_string('sortviewcount', 'repository_peertube')),
            (object)array('value' => '-likes', 'label' => get_string('sortlikes', 'repository_peertube'))
        );
        $sort->id = 'peertube_sort';
        $sort->name = 'peertube_sort';
        $sort->label = get_string('sortby', 'repository_peertube') . ': ';

        $ret['login'] = array($search, $sort);
        $ret['login_btn_label'] = get_string('search');
        $ret['login_btn_action'] = 'search';
        $ret['allowcaching'] = true;

        return $ret;
    }

    /**
     * Supported file types
     *
     * @return array
     */
    public function supported_filetypes() {
        return array('video');
    }

    /**
     * Supported return types
     *
     * @return int
     */
    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }

    /**
     * Check if repository contains private data
     *
     * @return bool
     */
    public function contains_private_data() {
        return false;
    }

    /**
     * Add plugin settings to form
     *
     * @param object $mform
     * @param string $classname
     */
    public static function type_config_form($mform, $classname = 'repository') {
        parent::type_config_form($mform, $classname);

        $instanceurl = get_config('peertube', 'instanceurl') ?: '';
        $mform->addElement('text', 'instanceurl', get_string('instanceurl', 'repository_peertube'),
            array('value' => $instanceurl, 'size' => '60'));
        $mform->setType('instanceurl', PARAM_URL);
        $mform->addRule('instanceurl', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('instanceurl', 'instanceurl', 'repository_peertube');

        $accesstoken = get_config('peertube', 'accesstoken') ?: '';
        $mform->addElement('text', 'accesstoken', get_string('accesstoken', 'repository_peertube'),
            array('value' => $accesstoken, 'size' => '60'));
        $mform->setType('accesstoken', PARAM_RAW_TRIMMED);
        $mform->addRule('accesstoken', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('accesstoken', 'accesstoken', 'repository_peertube');

        $mform->addElement('static', null, '', get_string('information', 'repository_peertube'));
    }

    /**
     * Get type option names
     *
     * @return array
     */
    public static function get_type_option_names() {
        return array('instanceurl', 'accesstoken', 'pluginname');
    }
}
