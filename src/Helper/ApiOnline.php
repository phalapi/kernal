<?php
namespace PhalApi\Helper;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'PhalApi.php';

class ApiOnline {

    protected $projectName;

    public function __construct($projectName) {
        $this->projectName = $projectName;
    }
}
