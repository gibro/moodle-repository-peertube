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
 * Strings for component 'repository_peertube', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   repository_peertube
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['apierror'] = '{$a}';
$string['accesstoken'] = 'Access Token';
$string['instanceurl'] = 'PeerTube Instance URL';
$string['information'] = 'Enter the URL of your PeerTube instance (e.g., https://peertube.example.com) and your access token. The access token can be generated in your PeerTube account settings. With a valid access token, you can search and access all videos you have permission to view, including unlisted videos.';
$string['pluginname'] = 'PeerTube videos';
$string['search'] = 'Search videos';
$string['peertube:view'] = 'Use PeerTube in file picker';
$string['configplugin'] = 'PeerTube repository type configuration';
$string['sortby'] = 'Sort By';
$string['sortpublished'] = 'Date Published (newest first)';
$string['sortpublishedasc'] = 'Date Published (oldest first)';
$string['sortlikes'] = 'Likes';
$string['sortviewcount'] = 'View Count';
$string['privacy:metadata:repository_peertube'] = 'The PeerTube videos repository plugin does not store any personal data, but does transmit user data from Moodle to the remote system.';
$string['privacy:metadata:repository_peertube:searchtext'] = 'The PeerTube videos repository user search text query';
$string['accesstoken_help'] = 'The access token for authenticating with the PeerTube API. You can generate this in your PeerTube account settings under "Applications" or "API". This token is required to access all videos you have permission to view, including unlisted and private videos.';
$string['instanceurl_help'] = 'The base URL of your PeerTube instance (e.g., https://peertube.example.com). Do not include a trailing slash.';
