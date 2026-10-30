<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'brand' => config('savoury.brand'),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'brand' => config('savoury.brand'),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.legal', [
            'title' => 'Privacy Policy',
            'intro' => 'This page will host the restaurant privacy policy once the owner supplies final wording.',
            'brand' => config('savoury.brand'),
        ]);
    }

    public function terms(): View
    {
        return view('pages.legal', [
            'title' => 'Terms and Conditions',
            'intro' => 'This page will host ordering terms once the owner supplies final wording.',
            'brand' => config('savoury.brand'),
        ]);
    }

    public function deliveryPolicy(): View
    {
        return view('pages.legal', [
            'title' => 'Delivery and Cancellation Policies',
            'intro' => 'Delivery zones, fees, and cancellation rules will appear here after owner confirmation. No fees or zones have been invented.',
            'brand' => config('savoury.brand'),
        ]);
    }

}
