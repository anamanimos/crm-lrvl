<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name'];

    public static function findOrCreateByName($name)
    {
        if (empty($name)) return null;
        
        $company = self::where('name', $name)->first();
        if (!$company) {
            $company = self::create(['name' => $name]);
        }
        
        return $company->id;
    }
}
