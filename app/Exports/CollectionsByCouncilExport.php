<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use App\Models\Collection;
use Maatwebsite\Excel\Concerns\FromView;

class CollectionsByCouncilExport implements FromView
{
    protected $councilId;

    public function __construct($councilId)
    {
        $this->councilId = $councilId;
    }

    public function view(): View
    {
        // Fetch collections for the specific council with their items
        $collections = Collection::where('council_id', $this->councilId)
            ->with('collectionItems')
            ->get();

        return view('exports.collections-by-council', [
            'collections' => $collections,
        ]);
    }
}
