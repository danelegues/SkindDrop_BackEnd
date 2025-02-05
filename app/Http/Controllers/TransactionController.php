<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Muestra una lista de transacciones.
     */
    public function index()
    {
        // Obtener todas las transacciones
        $transactions = Transaction::with(['user', 'item'])->get();

        return response()->json($transactions);
    }

    /**
     * Muestra un formulario para crear una nueva transacción.
     */
    public function create()
    {
        // Solo si necesitas un formulario en Blade, sino omite este método
    }

    /**
     * Guarda una nueva transacción en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'item_id' => 'required|exists:item,id',
            'type' => 'required|in:buy,sell,bid',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,completed,failed',
        ]);

        $transaction = Transaction::create($validated);

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => $transaction,
        ], 201);
    }

    /**
     * Muestra una transacción específica.
     */
    public function show($id)
    {
        $transaction = Transaction::with(['user', 'item'])->findOrFail($id);

        return response()->json($transaction);
    }

    /**
     * Muestra un formulario para editar una transacción existente.
     */
    public function edit($id)
    {
        // Solo si necesitas un formulario en Blade, sino omite este método
    }

    /**
     * Actualiza una transacción específica.
     */
    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'exists:users,id',
            'item_id' => 'exists:item,id',
            'type' => 'in:buy,sell,bid',
            'amount' => 'numeric|min:0',
            'status' => 'in:pending,completed,failed',
        ]);

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => $transaction,
        ]);
    }

    /**
     * Elimina una transacción específica.
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}