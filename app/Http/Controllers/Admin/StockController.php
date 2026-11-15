<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\StockMovementRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/**
 * Estoque de produtos (B-12 / RF07 / UC07).
 *
 * Controle manual: quem da entrada e saida e o admin. Baixa automatica por
 * servico realizado esta fora do escopo do TCC.
 */
class StockController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->when($request->input('search'), fn ($q, $busca) => $q->where('name', 'like', "%{$busca}%"))
            ->orderBy('name')
            ->get();

        // Alerta de reposicao (UC07-HU04): so entre os ativos, porque
        // produto desativado nao se compra mais.
        $alerts = Product::active()->belowMinimum()->orderBy('name')->get();

        return view('admin.stock.index', compact('products', 'alerts'));
    }

    public function create(): View
    {
        return view('admin.stock.create');
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create([
            'name' => $request->name,
            'unit' => $request->unit,
            'min_quantity' => $request->min_quantity,
            'quantity' => 0,
            'active' => true,
        ]);

        // O saldo informado no cadastro entra como movimentacao, e nao como
        // numero digitado direto: assim o primeiro saldo tambem tem historico.
        if ((float) $request->input('quantity', 0) > 0) {
            $product->registerMovement(
                Product::TYPE_IN,
                (float) $request->quantity,
                'Saldo inicial do cadastro.'
            );
        }

        return redirect()->route('admin.stock.index')
            ->with('success', 'Produto cadastrado.');
    }

    public function edit(Product $product): View
    {
        return view('admin.stock.edit', compact('product'));
    }

    /**
     * A edicao nao mexe no saldo de proposito (UC07-HU02): quantidade so
     * muda por entrada ou saida, para o numero nunca perder a explicacao.
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update([
            'name' => $request->name,
            'unit' => $request->unit,
            'min_quantity' => $request->min_quantity,
        ]);

        return redirect()->route('admin.stock.index')
            ->with('success', 'Produto atualizado.');
    }

    /**
     * Produto com movimentacao e inativado, nunca excluido (UC07-HU02) —
     * a mesma regra que vale para cliente e servico com historico.
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->movements()->exists()) {
            $product->update(['active' => false]);

            return redirect()->route('admin.stock.index')
                ->with('success', 'Produto inativado (possui movimentacoes registradas).');
        }

        $product->delete();

        return redirect()->route('admin.stock.index')
            ->with('success', 'Produto excluido.');
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->update(['active' => !$product->active]);
        $estado = $product->active ? 'ativado' : 'inativado';

        return redirect()->route('admin.stock.index')
            ->with('success', "Produto {$estado}.");
    }

    public function movements(Product $product): View
    {
        $movements = $product->movements()
            ->latest('created_at')
            ->latest('id')
            ->paginate(20);

        return view('admin.stock.movements', compact('product', 'movements'));
    }

    public function storeMovement(StockMovementRequest $request, Product $product): RedirectResponse
    {
        try {
            $product->registerMovement(
                $request->type,
                (float) $request->quantity,
                $request->notes
            );
        } catch (RuntimeException $e) {
            // Saida maior que o saldo: o UC07-HU03 pede aviso, nao erro 500.
            return redirect()->route('admin.stock.movements', $product)
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()->route('admin.stock.movements', $product)
            ->with('success', 'Movimentacao registrada.');
    }
}
