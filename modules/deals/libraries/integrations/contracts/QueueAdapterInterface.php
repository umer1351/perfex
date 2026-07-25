<?php

defined('BASEPATH') or exit('No direct script access allowed');

interface QueueAdapterInterface
{
    public function push(array $eventData);

    public function reserve($limit = 25);

    public function complete($id);

    public function fail($id, $message);
}
