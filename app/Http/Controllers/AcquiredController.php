<?php

namespace App\Http\Controllers;
   
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AcquiredController extends Controller
{
    
       /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }




    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * 
     *  */
    public function index(Request $request)
    {
        //Inventory Table
        //$articleid = $request->articleid;
        $description = $request->description;
        $unitofmeasure = $request->unitofmeasure;
        $unitvalue = $request->unitvalue;
        $date_acquired = $request->date_acquired;

        //Inventory Logs
        $propertynumber = $request->propertynumber;
        
        //Property
        //$statusid = $request->statusid;
        //$assignedto = $request->assignedto;
        //$location = $request->location;
        $remarks = $request->remarks;
        $tempname = $request->tempname;
        //$registered = $request->registered;

        $displayarticle = DB::table('article')
        ->select("article.id as id","article.code as code","article.article as article","category.category_name as categoryname")
        ->leftJoin('category', 'category.id', '=', 'article.category_id')
        ->get();

        $displayproperty = DB::table('inventory')
        ->select("inventory.id as id","category.category_name as category","article.article as article","inventory.description as description","inventory.quantity as unitmeasure","inventory.unit_value as value",
        "inventory.date_acquired as date_acquired", 
        "inventory.property_number as propertynumber",
        "inventory.status as status","inventory.assigned_to as assigned_to","inventory.remarks as remarks",
        "inventory.temp_name as tempname","inventory.registered_status as registeredstatus")
        ->leftJoin('article', 'article.id', '=', 'inventory.id_article')
        ->leftJoin('category', 'category.id', '=', 'article.category_id')
        ->where('inventory.in_status',1)
        ->get()
        ->toArray();

        $displaytransferredlogs = DB::table('property_logs')
        ->select("inventory.id as id","inventory.property_number as propnumber","property_logs.received_date as receiveddate",
        "property_logs.registered_status as regstatus","property_logs.assigned_to as assigned","property_logs.temp_name as tempname",
        "property_logs.status as status","property_logs.remarks as remarks")
        ->leftJoin('inventory', 'inventory.id', '=', 'property_logs.id_inventory')
        ->get();

        $displayemployee = DB::connection('mysql2')
        ->table('tbl_user')
        ->select("EMP_NO as id",DB::raw("CONCAT(NAME_F,' ',NAME_L) AS fullname"))
        ->get();


        return view('acquired.index',[
            'displayarticle' => $displayarticle,
            'displayemployee' => $displayemployee,
            'displayproperty' => $displayproperty,
            'displaytransferredlogs' => $displaytransferredlogs
        ]
        )
        ->with('description',$description)
        ->with('unitofmeasure',$unitofmeasure)
        ->with('unitvalue',$unitvalue)
        ->with('date_acquired',$date_acquired)
        ->with('propertynumber',$propertynumber)
        //->with('location',$location)
        ->with('remarks',$remarks)
        ->with('tempname',$tempname)
        ;

    }

    public function acquiredstore(Request $request){
        //Inventory Table
        $articleid = $request->articleid;
        $description = $request->description;
        $unitofmeasure = $request->unitofmeasure;
        $unitvalue = $request->unitvalue;
        $date_acquired = $request->date_acquired;
        $received_date = $request->received_date;

        //Inventory Logs
        $propertynumber = $request->propertynumber;
        
        //Property
        $statusid = $request->statusid;
        $assignedto = $request->assignedto;
        //$location = $request->location;
        $remarks = $request->remarks;
        $tempname = $request->tempname;
        $registered = $request->registered;

        if($registered == "YES"){

                    $messages =
                [
                    'articleid.required' => "Article Name is Required",

                    'description.required' => "Description is Required",
                    'unitofmeasure.required' => "Unit of Measure is required and accepts numbers only",
                    'unitvalue.required' => "Unit Value is required and accepts numbers only",
                    'date_acquired.required' => "Date Acquired is Required",
                    'propertynumber.required' => "Property Number is Required",

                    'statusid.required' => "Status of Equipment is Required",
                    'assignedto.required' => "Assigned Employee is Required",

                    //'location.required' => "Location is Required",
                    //'remarks.required' => "Category Name is Required",
                    //'tempname.required' => "Temporary Name is Required",

                    'registered.required' => "Registered Employee is Required",
                ];

                $rules = [

                    'articleid' => 'required',

                    'description' => 'required',
                    'unitofmeasure' => 'required',
                    'unitvalue' => 'required',
                    'date_acquired' => 'required',
                    'propertynumber' => 'required',

                    'statusid' => 'required',
                    'assignedto' => 'required',

                    //'location' => 'required',
                    //'remarks' => 'required',
                    //'tempname' => 'required',

                    'registered' => 'required',
                ];

                $validate =  Validator::make($request->all(),$rules,$messages);

                if($validate->fails()){

                    return redirect()->back()->withErrors($validate->messages())->withInput();
                }
                else {

                    $datainventorycreate=array('id_article'=>$articleid,'description'=>$description,'date_acquired'=>$date_acquired,
                    'property_number'=>$propertynumber, 'quantity'=>$unitofmeasure,'unit_value'=>$unitvalue,
                    'received_date'=>$received_date,'registered_status'=>$registered,'assigned_to'=>$assignedto,'temp_name'=>"",
                    'status'=>$statusid,'remarks'=>$remarks,
                    );
                    DB::table('inventory')->insertOrIgnore($datainventorycreate);


                    return redirect()->route('acquired.index')
                                    ->with('success','Article created successfully.');
                }

        }

        else if ($registered == "NO"){

                $messages =
                [
                    'articleid.required' => "Article Name is Required",

                    'description.required' => "Description is Required",
                    'unitofmeasure.required' => "Unit of Measure is required and accepts numbers only",
                    'unitvalue.required' => "Unit Value is required and accepts numbers only",
                    'date_acquired.required' => "Date Acquired is Required",
                    'propertynumber.required' => "Property Number is Required",

                    'statusid.required' => "Status of Equipment is Required",
                    //'assignedto.required' => "Assigned Employee is Required",

                    //'location.required' => "Location is Required",
                    //'remarks.required' => "Category Name is Required",
                    'tempname.required' => "Temporary Name is Required",

                    'registered.required' => "Registered Employee is Required",
                ];

                $rules = [

                    'articleid' => 'required',

                    'description' => 'required',
                    'unitofmeasure' => 'required',
                    'unitvalue' => 'required',
                    'date_acquired' => 'required',
                    'propertynumber' => 'required',

                    'statusid' => 'required',
                    //'assignedto' => 'required',

                    //'location' => 'required',
                    //'remarks' => 'required',
                    'tempname' => 'required',

                    'registered' => 'required',
                ];

                $validate =  Validator::make($request->all(),$rules,$messages);

                if($validate->fails()){

                    return redirect()->back()->withErrors($validate->messages())->withInput();
                }
                else {

                    $datainventorycreate=array('id_article'=>$articleid,'description'=>$description,'date_acquired'=>$date_acquired,
                    'property_number'=>$propertynumber, 'quantity'=>$unitofmeasure,'unit_value'=>$unitvalue,
                    'received_date'=>$received_date,'registered_status'=>$registered,'assigned_to'=>"",'temp_name'=>$tempname,
                    'status'=>$statusid,'remarks'=>$remarks,
                    );
                    DB::table('inventory')->insertOrIgnore($datainventorycreate);


                    return redirect()->route('acquired.index')
                                    ->with('success','Article created successfully.');
                }

        }

        else{

                $messages =
                [
                    'registered.required' => 'Please Input on the Necessary Information',
                ];

                $rules = [

                    'registered' => 'required',

                ];

                $validate =  Validator::make($request->all(),$rules,$messages);

                if($validate->fails()){

                    return redirect()->back()->withErrors($validate->messages())->withInput();
                }     
        }
    }

    public function transfermodalstore(Request $request){
        //Inventory Table
        $propertytransferid = $request->textid;
        $transmodalregistered = $request->transmodalregistered;
        $transmodalassignedto = $request->transmodalassignedto;
        $transmodaltempname = $request->transmodaltempname;
        $transmodaltransferred_date = $request->transmodaltransferred_date;
        $transmodalstatusid = $request->transmodalstatusid;
        $transmodallocation = $request->transmodallocation;
        $transmodalremarks = $request->transmodalremarks;


        /*$datainventory=array('property_id'=>$propertytransferid,'received_date'=>$transmodaltransferred_date,'location'=>$transmodallocation,
        'registered_status'=>$transmodalregistered,'assigned_to'=>$transmodalassignedto,'temp_name'=>$transmodaltempname,'status'=>$transmodalstatusid,'remarks'=>$transmodalremarks);
        DB::table('property_logs')->insertOrIgnore($datainventory);*/

        $datas = DB::table('inventory')
        ->where('id',$propertytransferid)
        ->first();

        $datasproperty_id = $datas->id;
        //$datasinventory_id = $datas->id_inventory;
        $datasreceived_date = $datas->received_date;
        //$dataslocation = $datas->location;
        $datasregistered_status = $datas->registered_status;
        $datasassigned_to = $datas->assigned_to;
        $datastemp_name = $datas->temp_name;
        $datasstatus = $datas->status;
        $datasremarks = $datas->remarks;

        $datainventory=array('id_inventory'=>$datasproperty_id,'received_date'=>$datasreceived_date,'registered_status'=>$datasregistered_status,
        'assigned_to'=>$datasassigned_to,'temp_name'=>$datastemp_name,'status'=>$datasstatus,'remarks'=>$datasremarks);
        DB::table('property_logs')->insertOrIgnore($datainventory);

        DB::table('inventory')
            ->where('id', $propertytransferid)
            ->update(['received_date' => $transmodaltransferred_date,
                      //'id_location'=>$transmodallocation,
                      'registered_status'=>$transmodalregistered,
                      'assigned_to'=>$transmodalassignedto,
                      'temp_name'=>$transmodaltempname,
                      'status'=>$transmodalstatusid,
                      'remarks'=>$transmodalremarks,
                    ]);

        //print_r($datainventory);
        return redirect()->route('acquired.index')
                        ->with('success','Article created successfully.');

    }

    public function edit($id)
    {
        $displayproperty = DB::table('inventory')
        ->select("inventory.id as id","category.category_name as category","article.id as articleid","article.article as article",
        "inventory.description as description","inventory.quantity as unitmeasure","inventory.unit_value as value",
        "inventory.date_acquired as date_acquired","inventory.property_number as propertynumber","inventory.received_date as received_date",
        "inventory.status as status","inventory.assigned_to as assigned_to","inventory.remarks as remarks",
        "inventory.temp_name as tempname","inventory.registered_status as registeredstatus")
        ->leftJoin('article', 'article.id', '=', 'inventory.id_article')
        ->leftJoin('category', 'category.id', '=', 'article.category_id')
        ->where('inventory.id',$id)
        ->get();

        $displayarticle = DB::table('article')
        ->select("article.id as id","article.code as code","article.article as article","category.category_name as categoryname")
        ->leftJoin('category', 'category.id', '=', 'article.category_id')
        ->get();

        $displayemployee = DB::connection('mysql2')
        ->table('tbl_user')
        ->select("EMP_NO as id",DB::raw("CONCAT(NAME_F,' ',NAME_L) AS fullname"))
        ->get();

        return view('acquired.edit',
        [
            'displayproperty'=>$displayproperty,
            'displayarticle'=>$displayarticle,
            'displayemployee' => $displayemployee
        ]
        );
    }

    public function update(Request $request,$id)
    {
        $editarticleid = $request->editarticleid;
        $editregistered = $request->editregistered;
        $editassignedto = $request->editassignedto;
        $edittempname = $request->edittempname;
        $editdescription = $request->editdescription;
        $editdate_acquired = $request->editdate_acquired;
        $editpropertynumber = $request->editpropertynumber;
        $editunitofmeasure = $request->editunitofmeasure;
        $editunitvalue = $request->editunitvalue;
        $editstatusid = $request->editstatusid;
        $editremarks = $request->editremarks;
        $editreceived_date = $request->editreceived_date;


        /*$request->validate([
            'categoryid' => 'required',
            //'code' => 'required',
            'article' => 'required',
        ]);*/
    
        DB::table('inventory')
        ->where('id', $id)
        ->update(['id_article' => $editarticleid,
                  'description'=>$editdescription,
                  'date_acquired'=>$editdate_acquired,
                  'property_number'=>$editpropertynumber,
                  'quantity'=>$editunitofmeasure,
                  'unit_value'=>$editunitvalue,
                  'received_date'=>$editreceived_date,
                  'registered_status'=>$editregistered,
                  'assigned_to'=>$editassignedto,
                  'temp_name'=>$edittempname,
                  'status'=>$editstatusid,
                  'remarks'=>$editremarks,
                ]);
    
        return redirect()->route('acquired.index')
                        ->with('success','Inventory updated successfully');
    }
    public function deactivate($id)
    {
        DB::table('inventory')
        ->where('id', $id)
        ->update(['in_status' => 0,
                ]);
    
        return redirect()->route('acquired.index')
                        ->with('success','Inventory deleted successfully');
    }
}
