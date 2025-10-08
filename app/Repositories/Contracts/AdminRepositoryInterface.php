<?php

namespace App\Repositories\Contracts;

interface AdminRepositoryInterface
{
   public function getTotalBarangKeluar();
    public function getTotalRequest();
    public function getTotalGuest();

    public function getLatestBarangKeluar($limit = 5);
    public function getLatestRequest($limit = 5);
    public function getTopRequesters($limit = 5);

    public function getChartDataByRange(string $range);
}
