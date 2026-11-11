<?php

namespace App\FormRules;


class StoreCustomerRules{
    public static function CUSTOMER(){
        return [
            'name' => ['required', 'regex:/^[ a-zA-Zぁ-んーァ-ヶー一-龠]+$/u' ,'max:50'],
            // 'kana' => ['required', 'regex:/^[ァ-ヾ]+$/u', 'max:50'],
            'kana' => ['required', 'regex:/^[ ァ-ヴーｦ-ﾟ]+$/u', 'max:50'],
            'tel' => ['required', 'max:20', 'unique:customers,tel'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'postcode' => ['required', 'max:7'],
            'address' => ['required', 'max:100'],
            'birthday' => ['date'],
            'gender' => ['required'],
            'memo' => ['max:1000'],
        ];
    }
}


