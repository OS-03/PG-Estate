<?php
require '/Users/owaisshaikh/Sites/PGLife/api/vendor/autoload.php';

use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
$cloudinary_config = [
    'cloud_name' => 'dmf0l0i74',
    'api_key'    => '654711718741794',
    'api_secret' => 'FbpxMA46mJvKvg0rIbq6yj37zxM'
];

Configuration::instance($cloudinary_config);
