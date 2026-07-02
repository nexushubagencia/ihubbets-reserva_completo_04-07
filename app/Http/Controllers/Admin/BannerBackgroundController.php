<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Site;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BannerBackgroundController extends Controller
{
    /**
     * Lista e permite upload de fundos para o Gerador de Banners.
     */
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        
        // Busca fundos do site atual OU fundos globais (site_id is null)
        $backgrounds = DB::table('banner_backgrounds')
            ->where('site_id', $siteId)
            ->orWhereNull('site_id')
            ->orderBy('site_id', 'asc') // Globais primeiro
            ->get();

        return view('admin.marketing.backgrounds', compact('backgrounds'));
    }

    /**
     * Salva um novo fundo personalizado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $siteId = config('tenant.site_id', 1);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners/backgrounds', 'public');

            // Se for SuperAdmin e marcar como global, site_id fica NULL
            $isGlobal = $request->has('is_global') && auth()->user()->role === 'admin';

            DB::table('banner_backgrounds')->insert([
                'site_id' => $isGlobal ? null : $siteId,
                'name' => $request->name,
                'path' => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Fundo personalizado carregado com sucesso!');
    }

    /**
     * Remove um fundo.
     */
    public function destroy($id)
    {
        $bg = DB::table('banner_backgrounds')->where('id', $id)->first();
        if ($bg) {
            Storage::disk('public')->delete($bg->path);
            DB::table('banner_backgrounds')->where('id', $id)->delete();
        }
        return redirect()->back()->with('success', 'Fundo removido.');
    }
}
