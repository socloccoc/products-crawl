<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Imports\StoreImport;
use App\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\DomCrawler\Crawler;

class MyController extends Controller
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function importExportView()
    {
        $stores = Store::all();
        return view('import', compact('stores'));
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function export()
    {
        ini_set('max_execution_time', 0);
        $stores = Store::where('id', '<', 50)->get();
        if (count($stores) == 0) {
            return redirect('/importExportView')->with('error_message', 'Không tìm thấy store, vui lòng import store sau đó thử lại !');
        }

        $data = [];
        foreach ($stores as $store) {
            $url = $store['url'];
            if (empty($url)) {
                continue;
            }
            $url = 'https://www.' . $this->cleanUrl($url);
            try {
                $crawler = new Crawler($this->crawlData($url));
                $crawler->filterXPath('//div[@class="TopCategoriesCollection mt-2 lg:pt-2"]')->each(function ($node) use (&$url, &$data) {
                    $node->filterXPath('//div[@class="flex flex-wrap"]/a')->each(function ($note) use (&$url, &$data) {
                        $categoryUrl = $url . $note->attr('href');
                        $categoryName = $note->text();
                        $products = $this->getProducts($url, $categoryUrl, $categoryName);
                        array_push($data, $products);
                    });
                });
            } catch (\Exception $ex) {
                continue;
            }
        }

        $prs = [];
        if (count($data) > 0) {
            foreach ($data as $item) {
                if (count($item) > 0) {
                    foreach ($item as $pr) {
                        $prs[] = $pr;
                    }
                }
            }
        }
        $header = $this->getHeader();
        $dataExcel = new ProductsExport([$prs], $header);

        $excel = Excel::download($dataExcel, "products-" . Carbon::now()->format('Y-m-d-his') . ".xlsx");

        return $excel;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function import()
    {
        try {
            Store::truncate();
            Excel::import(new StoreImport, request()->file('file'));
            return redirect('/importExportView')->with('message', 'Import thành không !');
        } catch (\Exception $ex) {
            return redirect('/importExportView')->with('error_message', 'Import thất bại !');
        }
    }

    public function getProducts($url, $categoryUrl, $categoryName)
    {
        $products = [];
        $pageCount = $this->countOfPage($categoryUrl);
        $pageCount = $pageCount == 0 ? 1 : $pageCount;
        for ($i = 1; $i <= $pageCount; $i++) {
            $categoryUrl = $categoryUrl . '/page/' . $i;
            $crawler = new Crawler($this->crawlData($categoryUrl));
            $crawler->filterXPath('//div[@class="ui three doubling product cards m-t-0"]/div')->each(function ($node) use (&$url, &$categoryName, &$products) {
                $productUrl = $url . $node->filter('a')->attr('href');
                $productName = $node->filter('span')->text();
                $products[] = [$categoryName, $productUrl, $productName];
            });
        }
        return $products;
    }

    public function countOfPage($categoryUrl)
    {
        $crawler = new Crawler($this->crawlData($categoryUrl));
        $pageCount = 0;
        $crawler->filterXPath('//span[@class="PaginationMenu"]/div/a')->each(function ($node) use (&$pageCount) {
            if ($node->attr('class') == 'entity-primary-hovered-bg tablet-only computer-only item') {
                $pageCount = $node->text();
            }
        });
        return $pageCount;
    }

    public function crawlData($url)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 50,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_HTTPHEADER     => array(
                "cache-control: no-cache",
                "postman-token: b03e3841-eb75-9842-5eb9-67da902ad8f5"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function cleanUrl($content)
    {
        $string = htmlentities($content, null, 'utf-8');
        $content = str_replace("&nbsp;", "", $string);
        $content = html_entity_decode($content);
        return $content;
    }

    private function getHeader()
    {
        return $header = [
            'Category',
            'ProductUrl',
            'ProductName'
        ];
    }
}