<?php

namespace App\Imports;

use App\Store;
use Maatwebsite\Excel\Concerns\ToModel;

class StoreImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Store([
            'url' => $row[0],
        ]);
    }
}
