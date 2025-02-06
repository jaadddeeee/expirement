<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use ROLE;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   *
   * @return void
   */
  public function register()
  {
    //
  }

  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {

    view()->composer('*', function($view)
    {
        $menuTeacher = [];
        $menuClearance = [];
        $verticalMenuData = [];
        $menuNSTP = [];
        $menuRegistrar = [];
        $menuOSAS = [];
        $menuCashier = [];
        $menuTES = [];
        $menuUISA = [];
        $menuPresident = [];
        $menuVPAA = [];
        $menuScholar = [];

        $menuAll = file_get_contents(base_path('resources/menu/verticalMenuAll.json'));
        array_push($verticalMenuData, json_decode($menuAll));

        if (ROLE::isTeacher()){
            $menuTeacher = file_get_contents(base_path('resources/menu/verticalMenuTeacher.json'));
            array_push($verticalMenuData, json_decode($menuTeacher));
        }

        if (ROLE::isPresident()){
          $menuPresident = file_get_contents(base_path('resources/menu/verticalMenuPresident.json'));
          array_push($verticalMenuData, json_decode($menuPresident));
        }

        if (ROLE::isVPAA()){
          $menuVPAA = file_get_contents(base_path('resources/menu/verticalMenuVPAA.json'));
          array_push($verticalMenuData, json_decode($menuVPAA));
        }

        if (ROLE::isRegistrar()){
          $menuRegistrar = file_get_contents(base_path('resources/menu/verticalMenuRegistrar.json'));
          array_push($verticalMenuData, json_decode($menuRegistrar));
        }

        if (ROLE::isUISA()){
          $menuUISA = file_get_contents(base_path('resources/menu/verticalMenuUISA.json'));
          array_push($verticalMenuData, json_decode($menuUISA));
        }

        if (ROLE::isCashier()){
          $menuCashier = file_get_contents(base_path('resources/menu/verticalMenuCashier.json'));
          array_push($verticalMenuData, json_decode($menuCashier));
        }

        if (ROLE::isDepartment()){
          $menuDepartment = file_get_contents(base_path('resources/menu/verticalMenuDean.json'));
          array_push($verticalMenuData, json_decode($menuDepartment));
        }

        if (ROLE::isClearance()){
          $menuClearance = file_get_contents(base_path('resources/menu/verticalMenuClearance.json'));
          array_push($verticalMenuData, json_decode($menuClearance));
        }

        if (ROLE::isNSTP()){
          $menuNSTP = file_get_contents(base_path('resources/menu/verticalMenuNSTP.json'));
          array_push($verticalMenuData, json_decode($menuNSTP));
        }

        if (ROLE::isOSAS()){
          $menuOSAS= file_get_contents(base_path('resources/menu/verticalMenuOSAS.json'));
          array_push($verticalMenuData, json_decode($menuOSAS));
        }

        if (ROLE::isTES()){
          $menuTES= file_get_contents(base_path('resources/menu/verticalMenuTES.json'));
          array_push($verticalMenuData, json_decode($menuTES));
        }

        if (ROLE::isScholarship()){
          $menuTES= file_get_contents(base_path('resources/menu/verticalMenuScholarship.json'));
          array_push($verticalMenuData, json_decode($menuTES));
        }

        if (ROLE::isScholar()){
          $menuScholar = file_get_contents(base_path('resources/menu/verticalMenuScholarshipNew.json'));
          array_push($verticalMenuData, json_decode($menuScholar));
        }
        
        \View::share('menuData', $verticalMenuData);
    });
  }
}
