<?php

namespace Amalgam;

use yii\base\Event as BaseEvent;

class Event extends BaseEvent
{
    public $command;
    public $error;
}
