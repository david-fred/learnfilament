<?php

use App\Models\Order;
use Livewire\Component;


new class extends Component
{
    public string $search = '';

    public function render()
    {
        $results = [];

        if (strlen($this->search) > 2) {
            $results = Order::whereHas('product', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })->limit(5)->get();
        }

        return view('livewire.admin-search-bar', [
            'results' => $results,
        ]);
    }
};
?>

<div>
    {{-- He who is contented is rich. - Laozi --}}
</div>