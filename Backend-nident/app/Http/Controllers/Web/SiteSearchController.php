<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\SiteSearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSearchController extends Controller
{
    public function __construct(private SiteSearchService $searchService)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $query = trim((string) $request->input('q', ''));

        if ($query === '') {
            return redirect()->route('home');
        }

        $result = $this->searchService->findBest($query);

        return redirect()->to($result['url']);
    }
}
