<?php

namespace App\Helpers;
 
use Illuminate\Pagination\BootstrapFourPresenter;

class PaginationPresenter extends BootstrapFourPresenter {

    public function hasPages()
    {
        return true;
    }

}

?> 