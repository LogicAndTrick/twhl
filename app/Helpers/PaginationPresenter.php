<?php

namespace App\Helpers;
 
use Illuminate\Pagination\BootstrapThreePresenter;

class PaginationPresenter extends BootstrapThreePresenter {

    public function hasPages()
    {
        return true;
    }

}

?> 