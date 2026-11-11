<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\FilterRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Domains\Customer\UseCases\Customer\FilterAction;
use App\FormRules\StoreCustomerRules;
use App\Http\Requests\Customer\ImportRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CustomerController extends Controller
{
    private int $CSV_DATA_NUMBER = 100;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(
        Request $request,
        FilterAction $action,
    ) {
        $perPage = $request['perPage'] ? $request['perPage'] : 5;

        $customers = $action->invoke($request)
            ->select('id', 'name', 'kana', 'tel', 'email')
            ->paginate($perPage)
            ->withQueryString();

        $filters = $request->only(
            [
                'searchId',
                'searchName',
                'searchKana',
                'searchTel',
                'searchEmail',
                'perPage',
            ]
        );

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $filters,
        ]);
    }

    public function import(ImportRequest $request){

        $header = [
        '名前',
        'カナ',
        '電話番号',
        'メールアドレス',
        '郵便番号',
        '住所',
        '誕生日',
        '性別',
        'メモ',];

        $path = $request->file()->getRealPath();

        if($path === false){
            throw new RuntimeException("ファイルパスが取得できません");
        }

        $stream = fopen($path,'r');

        if($stream === false){
            throw new RuntimeException("ファイルを開くことができません");
        }

        $contents = file_get_contents($path);

        if($contents === false){
            throw new RuntimeException('ファイルを読み取れません');
        }

        if(mb_check_encoding($contents,'UTF-8')){
            throw new RuntimeException('UTF-8形式のファイルを指定してください');
        }

        // BOM対策（BOMがなければ読み込み位置を戻す）
        if(fread($path,3) !== 'xEF/xBB/xBF'){
            rewind($stream);
        }

        // ヘッダ行がないCSV向けのファイルポインタ調整
        if(fgetcsv($stream) !== $header){
            fseek($stream,4);
        }

        $validatedRows = [];
        $recordNumber = 0;

        while(($row = fgetcsv($stream)) !== false){
            if($row === [null]){
                continue;
            }

            $recordNumber++;

            if($recordNumber > $this->CSV_DATA_NUMBER){
                throw new ValidationException("データ数は$this->CSV_DATA_NUMBER 以内にしてください");
            }

            if(count($row) !== 9){
                throw new ValidationException("$recordNumber 行目のデータ件数が足りていません");
            }

            $data = array_combine($header,$row);

            // 余計な空白の除去
            $data = array_map('trim',$data);

            if($data['誕生日'] === ''){
                $data['誕生日'] = null;
            }

            if($data['メモ'] === ''){
                $data['メモ'] = null;
            }

            $rules = StoreCustomerRules::CUSTOMER();

            $validator = Validator::make($data,$rules);


            if($validator->fails()){
                throw new ValidationException("$recordNumber 行目のデータの形式に誤りがあります");
            }







        }






    }

    public function exportAll()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Excelで文字化けしないようにBOMを付与
            fwrite($handle, "\xEF\xBB\xBF");

            // CSVヘッダー
            fputcsv($handle, [
                '氏名',
                'フリガナ',
                '電話番号',
                'メールアドレス',
            ]);

            // データを取得してCSVに書き込む
            foreach (
                Customer::query()
                ->select('id', 'name', 'kana', 'tel', 'email')
                ->lazyById(1000) as $customer
                ){
                fputcsv($handle, [
                    $customer->name,
                    $customer->kana,
                    $customer->tel,
                    $customer->email,
                ]);
            }

            fclose($handle);
        }, 'allCustomers.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]
        );
    }

    public function exportSearch(FilterRequest $request, FilterAction $action)
    {
        $query = $action->invoke(new Request($request->validated()));

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['氏名', 'フリガナ', '電話番号', 'メールアドレス']);

            foreach (
                $query->select('id', 'name', 'kana', 'tel', 'email')
                    ->lazyById(1000) as $customer
            ) {
                fputcsv($handle, [
                    $customer->name,
                    $customer->kana,
                    $customer->tel,
                    $customer->email,
                ]);
            }

            fclose($handle);
        }, 'searchedCustomers.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerRequest $request)
    {
        Customer::create([
            'name' => $request->name,
            'kana' => $request->kana,
            'tel' => $request->tel,
            'email' => $request->email,
            'postcode' => $request->postcode,
            'address' => $request->address,
            'birthday' => $request->birthday,
            'gender' => $request->gender,
            'memo' => $request->memo,
        ]);

        return to_route('customers.index')
            ->with([
                'message' => '登録しました。',
                'status' => 'success'
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer)
    {
        //  dd($customer);
        return Inertia::render("Customers/Show", [
            'customer' => $customer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomerRequest  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        // dd($request->name, $customer->name);
        $customer->name = $request->name;
        $customer->kana = $request->kana;
        $customer->tel = $request->tel;
        // $customer->email = $request->email;
        $customer->postcode = $request->postcode;
        $customer->address = $request->address;
        $customer->birthday = $request->birthday;
        $customer->gender = $request->gender;
        $customer->memo = $request->memo;
        $customer->save();

        return to_route('customers.index')->with([
            'message' => '更新しました。',
            'status' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return to_route('customers.index')->with([
            'message' => '削除しました。',
            'status' => 'danger'
        ]);
    }
}
