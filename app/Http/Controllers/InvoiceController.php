<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Rawilk\Printing\Facades\Printing;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * The number of attempts to retrying.
     *
     * @var int
     */
    private $counter = 0;

    /**
     * Download and print invoice.
     *
     * @param \Illuminate\Http\Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function printInvoice(Request $request): JsonResponse
    {
        if ($this->counter == 3) {
            return response()->json();
        }

        $this->counter++;
        $filePath = parse_url($request->invoiceUrl, PHP_URL_PATH);
        $parts = explode("/", $filePath);
        $invoiceName = end($parts);

        try {
            Storage::disk('public')->put("/invoices/$invoiceName", file_get_contents($request->invoiceUrl));
        } catch (\Throwable $th) {
            report($th);
            sleep(10);
            return $this->printInvoice($request);
        }

        $printJob = Printing::newPrintTask()
            ->printer(env('CUPS_PRINTER_ID'))
            ->file(storage_path() . "/app/public/invoices/$invoiceName")
            ->send();

        return response()->json($printJob->id()); // the id number returned from the print server
    }
}
