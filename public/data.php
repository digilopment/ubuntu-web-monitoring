<?php
include '../src/SystemInfo.php';

(new SystemInfo())->getData()->withJson()->render();
