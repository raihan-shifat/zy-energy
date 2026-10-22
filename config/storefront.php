<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Category lists on the storefront
    |--------------------------------------------------------------------------
    |
    | How many active categories the homepage "Product Categories" grid and the
    | header "Products" menu show (in the admin's sort order). Empty or 0 means
    | all active categories. Which categories appear is controlled in the admin
    | (category status and Move up / Move down).
    |
    */

    'home_categories_limit' => (int) env('HOME_CATEGORIES_LIMIT', 0),

    'menu_categories_limit' => (int) env('MENU_CATEGORIES_LIMIT', 0),

];
