<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Counter;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function get_all_invoice(){
        $invoices = Invoice::with('customer')->orderby('id', 'DESC')->get();

        return response()->json([
            'invoices' => $invoices
        ], 200);
    }

    public function search_invoice(Request $request){
        $search = $request->get('s');
        if($search != null){
            $invoices = Invoice::with('customer')->where('id','LIKE',"%$search%")->get();

            return response()->json([
                'invoices' => $invoices
            ], 200);
        }else{
            return $this->get_all_invoice();
        }
    }

    public function create_invoice(){
        $counter = Counter::where('key', 'invoice')->first();
        $random = Counter::where('key', 'invoice')->first();

        $invoice = Invoice::orderBy('id', 'DESC')->first();

        if($invoice){
            $invoice = $invoice->id+1;
            $counters = $counter->value + $invoice;
        }else{
            $counters = $counter->value;
        }

        $formData = [
            'number' => $counter->prefix.$counters,
            'customer_id' => null,
            'customer' => null,
            'date' => date('Y-m-d'),
            'due_date' => null,
            'reference' => null,
            'discount' => 0,
            'terms_and_conditions' => 'Default Terms and Conditions',
            'invoice_item' => [
                'product_id' => null,
                'product' => null,
                'unit_price' => 0,
                'quantity' => 1
            ]
        ];

        return response()->json($formData);
    }

    public function add_invoice(Request $request){
        $invoiceitem = $request->input("invoice item");

        $invoicedata["sub_total"] = $request->input("subTotal");
        $invoicedata["total"] = $request->input("total");
        $invoicedata["customer_id"] = $request->input("customer_id");
        $invoicedata["date"] = $request->input("date");
        $invoicedata["due_date"] = $request->input("due_date");
        $invoicedata["number"] = $request->input("number");
        $invoicedata["reference"] = $request->input("reference");
        $invoicedata["discount"] = $request->input("discount");
        $invoicedata["terms_and_conditions"] = $request->input("terms_and_conditions");

        $invoice = Invoice::create($invoicedata);

        foreach(json_decode($invoiceitem) as $item){
            $itemdata['product_id'] = $item->id;
            $itemdata['invoice_id'] = $invoice->id;
            $itemdata['quantity'] = $item->quantity;
            $itemdata['unit_price'] = $item->unit_price;

            InvoiceItem::create($itemdata);

        }
    }

    public function show_invoice($id){
        $invoice = Invoice::with(['customer', 'invoice_items.product'])->find($id);

        return response()->json([
            'invoice' => $invoice
        ], 200);
    }
}
