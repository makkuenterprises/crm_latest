<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Password extends Model
{
    use HasFactory;

    public function customer()
    {
        return $this->belongsTo(Customer::class); // Assuming 'customer_id' is the foreign key
    }
}