<?php
/**
 * Created by PhpStorm.
 * User: eater
 * Date: 3/30/16
 * Time: 12:10 AM
 */

namespace Eater\Glim\Handler;

class Home extends Main
{
    function handle()
    {
        $throw = $this->get('die')->throwDie('1d6');
        
        return $this->render("home.html", [
            'name'   => $this->get('name'),
            'author' => $this->get('author'),
            'throw'  => $throw
        ]);
    }
}