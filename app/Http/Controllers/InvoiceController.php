<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rawilk\Printing\Facades\Printing;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function printInvoice(Request $request)
    {
        $filePath = parse_url($request->invoiceUrl, PHP_URL_PATH);
        $parts = explode("/", $filePath);
        $invoiceName = end($parts);
        Storage::disk('public')->put("/invoices/$invoiceName", file_get_contents($request->invoiceUrl));

        $printJob = Printing::newPrintTask()
            ->printer(env('CUPS_PRINTER_ID'))
            ->file(storage_path() . "/app/public/invoices/$invoiceName")
            ->send();

        return response()->json($printJob->id()); // the id number returned from the print server
    }
}
