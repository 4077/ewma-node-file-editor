<?php namespace ewma\nodeFileEditor\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function update()
    {
        $this->d('~:cache|', $this->data('value'), RR);

        $this->c('~|')->performCallback('update');
    }
}
