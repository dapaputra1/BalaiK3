<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\ServiceParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::with(['items.serviceParameter.category'])
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json([
                'items' => [],
                'count' => 0,
                'subtotal' => 0,
            ]);
        }

        $categoryOrder = [
            'Lingkungan Kerja' => 1,
            'Ambien' => 2,
            'Emisi' => 3,
            'Kesehatan' => 4,
            'Pelatihan' => 5,
            'Paket Emisi' => 6,
        ];

        $regularItems = $cart->items
            ->whereNull('package_key')
            ->map(function ($item) {
                return [
                    'id' => $item->service_parameter_id,
                    'item_type' => 'parameter',
                    'name' => $item->serviceParameter?->name ?? '-',
                    'category' => $item->serviceParameter?->category?->name ?? '',
                    'sort_id' => $item->id,
                    'price' => (float) $item->price,
                    'qty' => $item->qty,
                    'subtotal' => (float) ($item->price * $item->qty),
                ];
            })
            ->values();

        $packageItems = $cart->items
            ->whereNotNull('package_key')
            ->groupBy('package_key')
            ->map(function ($group) {
                $first = $group->first();
                $details = collect($first?->package_details ?? [])
                    ->map(fn ($item) => (string) $item)
                    ->filter()
                    ->values()
                    ->all();

                $price = (float) ($first?->package_price ?? 0);

                return [
                    'id' => 'pkg:' . (string) $first->package_key,
                    'item_type' => 'package',
                    'package_key' => (string) $first->package_key,
                    'name' => $first->package_name ?: 'Paket Emisi',
                    'category' => 'Paket Emisi',
                    'sort_id' => (int) $group->min('id'),
                    'details' => $details,
                    'price' => $price,
                    'qty' => 1,
                    'subtotal' => $price,
                ];
            })
            ->values();

        $items = $regularItems
            ->concat($packageItems)
            ->sortBy([
                fn ($a, $b) => ($categoryOrder[$a['category']] ?? 99) <=> ($categoryOrder[$b['category']] ?? 99),
                fn ($a, $b) => ($a['sort_id'] ?? PHP_INT_MAX) <=> ($b['sort_id'] ?? PHP_INT_MAX),
            ])
            ->map(function (array $item) {
                unset($item['sort_id']);

                return $item;
            })
            ->values();
        $subtotal = $items->sum('subtotal');
        $count = $items->sum('qty');

        return response()->json([
            'items' => $items,
            'count' => $count,
            'subtotal' => $subtotal,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_parameter_id' => ['required', 'exists:service_parameters,id'],
        ]);

        $serviceParameter = ServiceParameter::findOrFail($data['service_parameter_id']);
        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
            'status' => 'active',
        ]);

        $item = $cart->items()
            ->whereNull('package_key')
            ->where('service_parameter_id', $serviceParameter->id)
            ->first();

        if ($item) {
            $item->qty += 1;
            $item->save();
        } else {
            $cart->items()->create([
                'service_parameter_id' => $serviceParameter->id,
                'qty' => 1,
                'price' => $serviceParameter->price,
            ]);
        }

        return $this->index();
    }

    public function storePackage(Request $request)
    {
        $data = $request->validate([
            'package_name' => ['required', 'string', 'max:255'],
            'package_price' => ['required', 'numeric', 'min:0'],
            'package_details' => ['required', 'array', 'min:1'],
            'package_details.*' => ['string', 'max:255'],
            'service_parameter_ids' => ['required', 'array', 'min:1'],
            'service_parameter_ids.*' => ['integer', 'exists:service_parameters,id'],
        ]);

        $serviceParameterIds = collect($data['service_parameter_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($serviceParameterIds)) {
            return response()->json([
                'message' => 'Paket tidak memiliki parameter yang valid.',
            ], 422);
        }

        $serviceParameters = ServiceParameter::whereIn('id', $serviceParameterIds)
            ->get()
            ->keyBy('id');

        $cart = Cart::firstOrCreate([
            'user_id' => auth()->id(),
            'status' => 'active',
        ]);

        $packageKey = (string) Str::uuid();
        $packageName = (string) $data['package_name'];
        $packagePrice = (float) $data['package_price'];
        $packageDetails = collect($data['package_details'])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        foreach ($serviceParameterIds as $serviceParameterId) {
            $parameter = $serviceParameters->get($serviceParameterId);
            if (!$parameter) {
                continue;
            }

            $cart->items()->create([
                'service_parameter_id' => $serviceParameterId,
                'qty' => 1,
                'price' => $parameter->price,
                'package_key' => $packageKey,
                'package_name' => $packageName,
                'package_price' => $packagePrice,
                'package_details' => $packageDetails,
            ]);
        }

        return $this->index();
    }

    public function update(Request $request, ServiceParameter $service_parameter)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        $item = $cart->items()
            ->whereNull('package_key')
            ->where('service_parameter_id', $service_parameter->id)
            ->firstOrFail();

        $item->qty = $data['qty'];
        $item->save();

        return $this->index();
    }

    public function destroy(ServiceParameter $service_parameter)
    {
        $cart = Cart::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if ($cart) {
            $cart->items()
                ->whereNull('package_key')
                ->where('service_parameter_id', $service_parameter->id)
                ->delete();
        }

        return $this->index();
    }

    public function destroyPackage(string $packageKey)
    {
        $cart = Cart::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if ($cart) {
            $cart->items()
                ->where('package_key', $packageKey)
                ->delete();
        }

        return $this->index();
    }

    public function clear()
    {
        $cart = Cart::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return $this->index();
    }
}
