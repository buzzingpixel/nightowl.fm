<?php

declare(strict_types=1);

foreach (glob('config/Data/Migrations/*.php') as $filename) {
    include_once $filename;
}
