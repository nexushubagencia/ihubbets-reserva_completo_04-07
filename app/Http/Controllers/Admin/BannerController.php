<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $siteId = config('tenant.site_id', 1);
        $banners = Banner::where('site_id', $siteId)->orderBy('id', 'DESC')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $siteId = config('tenant.site_id', 1);
        \Log::info('Tentando salvar banner. Site ID: ' . $siteId);
        
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            
            // Salva na pasta do Tenant
            $fileName = time() . '_' . $file->getClientOriginalName();
            $tenantDir = 'storage/tenant_' . $siteId . '/banners';
            if (!file_exists(public_path($tenantDir))) { mkdir(public_path($tenantDir), 0755, true); }
            $file->move(public_path($tenantDir), $fileName);
            $imagePath = $tenantDir . '/' . $fileName;

            $banner = Banner::create([
                'site_id' => $siteId,
                'title' => $request->title ?? 'Banner Sem Nome',
                'image_path' => $imagePath,
                'link' => $request->link_url ?? '#',
                'display_to' => $request->display_to ?? 'all',
                'position' => $request->position ?? 'home_main',
                'status' => 1
            ]);

            \Log::info('Banner criado no banco direto! ID: ' . $banner->id);
            return redirect()->back()->with('success', 'Banner adicionado com sucesso!');
        }

        \Log::error('Falha: Nenhum arquivo de imagem recebido no request.');
        return redirect()->back()->with('error', 'Nenhuma imagem foi enviada.');
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'link_url' => 'nullable|string',
            'display_to' => 'nullable|string',
            'status' => 'required|integer'
        ]);

        $updateData = [
            'title' => $request->title,
            'link' => $request->link_url,
            'display_to' => $request->display_to,
            'position' => $request->position,
            'status' => $request->status
        ];

        if ($request->hasFile('image_file')) {
            // Remove antiga (tenta do storage e direto do public_path)
            $oldPathStorage = str_replace('storage/', 'public/', $banner->image_path);
            Storage::delete($oldPathStorage);
            
            $oldPhysicalPath = public_path($banner->image_path);
            if(file_exists($oldPhysicalPath) && is_file($oldPhysicalPath)) {
                @unlink($oldPhysicalPath);
            }

            $file = $request->file('image_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $siteId = $banner->site_id;
            $tenantDir = 'storage/tenant_' . $siteId . '/banners';
            if (!file_exists(public_path($tenantDir))) { mkdir(public_path($tenantDir), 0755, true); }
            $file->move(public_path($tenantDir), $fileName);
            $updateData['image_path'] = $tenantDir . '/' . $fileName;
        }

        $banner->update($updateData);

        return redirect()->back()->with('success', 'Banner atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        
        if ($banner->image_path) {
            $physicalPathStorage = str_replace('storage/', 'public/', $banner->image_path);
            Storage::delete($physicalPathStorage);

            $physicalPath = public_path($banner->image_path);
            if(file_exists($physicalPath) && is_file($physicalPath)) {
                @unlink($physicalPath);
            }
        }
        
        $banner->delete();

        return redirect()->back()->with('success', 'Banner removido!');
    }

    public function toggleStatus($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->status = !$banner->status;
        $banner->save();

        return redirect()->back()->with('success', 'Status do banner alterado!');
    }

    public function generatorView()
    {
        return view('admin.banners.generator');
    }
}
