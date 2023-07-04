<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rawilk\Printing\Facades\Printing;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function printInvoice(Request $request)
    {
        $invoiceName = str_replace('https://astana.sf-kiosk.kz/storage/invoices/', '', $request->invoiceUrl);
        Storage::disk('public')->put("/invoices/$invoiceName", file_get_contents($request->invoiceUrl));

        $printJob = Printing::newPrintTask()
            ->printer('ipp://127.0.0.1:631/printers/FieryPrinter')
            ->file(storage_path() . "/app/public/invoices/$invoiceName")
            ->send();

        return response()->json($printJob->id()); // the id number returned from the print server
    }
}
