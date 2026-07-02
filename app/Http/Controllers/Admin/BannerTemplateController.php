<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannerTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerTemplateController extends Controller
{
    /** Listagem de templates */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $q    = BannerTemplate::orderByDesc('active')->orderBy('name');
        if ($type) $q->where('type', $type);

        return response()->json([
            'data' => $q->get()->map(fn($t) => $this->format($t))
        ]);
    }

    /** Template ativo por tipo */
    public function active(Request $request)
    {
        $type = $request->query('type', 'single');
        $tpl  = BannerTemplate::where('type', $type)->where('active', true)->first();
        if (!$tpl) return response()->json([]);

        return response()->json([
            'accentColor'    => $tpl->accent_color,
            'backgroundUrl'  => $tpl->background_url ? Storage::url($tpl->background_url) : null,
            'overlayOpacity' => $tpl->overlay_opacity,
        ]);
    }

    public function show(BannerTemplate $bannerTemplate)
    {
        return response()->json($this->format($bannerTemplate));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:80',
            'type'             => 'required|in:single,multi',
            'accent_color'     => 'required|string|max:10',
            'overlay_opacity'  => 'numeric|min:0|max:1',
            'background_image' => 'nullable|image|max:5120',
        ]);

        $bgPath = null;
        if ($request->hasFile('background_image')) {
            $bgPath = $request->file('background_image')
                ->store('banner-templates/backgrounds', 'public');
        } elseif ($request->filled('background_external_url')) {
            $bgPath = $request->input('background_external_url');
        }

        $previewPath = null;
        if ($request->filled('preview_data_url')) {
            $previewPath = $this->saveDataUrl(
                $request->input('preview_data_url'),
                'banner-templates/previews'
            );
        }

        if ($request->boolean('active')) {
            BannerTemplate::where('type', $request->type)->update(['active' => false]);
        }

        $tpl = BannerTemplate::create([
            'name'             => $request->name,
            'type'             => $request->type,
            'accent_color'     => $request->accent_color,
            'overlay_opacity'  => $request->input('overlay_opacity', 0.55),
            'active'           => $request->boolean('active'),
            'background_url'   => $bgPath,
            'preview_url'      => $previewPath,
        ]);

        return response()->json(['success' => true, 'data' => $this->format($tpl)]);
    }

    public function update(Request $request, BannerTemplate $bannerTemplate)
    {
        $request->validate([
            'name'             => 'required|string|max:80',
            'type'             => 'required|in:single,multi',
            'accent_color'     => 'required|string|max:10',
            'overlay_opacity'  => 'numeric|min:0|max:1',
            'background_image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('background_image')) {
            if ($bannerTemplate->background_url && !filter_var($bannerTemplate->background_url, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($bannerTemplate->background_url);
            }
            $bannerTemplate->background_url = $request->file('background_image')
                ->store('banner-templates/backgrounds', 'public');
        } elseif ($request->filled('background_external_url')) {
            $bannerTemplate->background_url = $request->input('background_external_url');
        }

        if ($request->filled('preview_data_url')) {
            if ($bannerTemplate->preview_url) {
                Storage::disk('public')->delete($bannerTemplate->preview_url);
            }
            $bannerTemplate->preview_url = $this->saveDataUrl(
                $request->input('preview_data_url'),
                'banner-templates/previews'
            );
        }

        if ($request->boolean('active')) {
            BannerTemplate::where('type', $request->type)
                          ->where('id', '!=', $bannerTemplate->id)
                          ->update(['active' => false]);
        }

        $bannerTemplate->fill([
            'name'            => $request->name,
            'type'            => $request->type,
            'accent_color'    => $request->accent_color,
            'overlay_opacity' => $request->input('overlay_opacity', 0.55),
            'active'          => $request->boolean('active'),
        ])->save();

        return response()->json(['success' => true, 'data' => $this->format($bannerTemplate)]);
    }

    public function activate(BannerTemplate $bannerTemplate)
    {
        BannerTemplate::where('type', $bannerTemplate->type)->update(['active' => false]);
        $bannerTemplate->update(['active' => true]);
        return response()->json(['success' => true]);
    }

    public function destroy(BannerTemplate $bannerTemplate)
    {
        if ($bannerTemplate->background_url && !filter_var($bannerTemplate->background_url, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($bannerTemplate->background_url);
        }
        if ($bannerTemplate->preview_url) {
            Storage::disk('public')->delete($bannerTemplate->preview_url);
        }
        $bannerTemplate->delete();
        return response()->json(['success' => true]);
    }

    /** View do gerador de banners */
    public function geradorView()
    {
        $siteId = config('tenant.site_id', 1);
        $site   = \App\Models\Site::find($siteId);
        $logo   = $site ? asset('storage/' . ltrim($site->logo ?? 'images/logo.png', 'storage/')) : asset('images/logo.png');

        return view('admin.banners.generator', [
            'siteLogo' => $logo,
            'siteUrl'  => config('app.url'),
            'siteInstagram' => $site->social_instagram ?? '',
        ]);
    }

    private function format(BannerTemplate $t): array
    {
        return [
            'id'             => $t->id,
            'name'           => $t->name,
            'type'           => $t->type,
            'accentColor'    => $t->accent_color,
            'overlayOpacity' => $t->overlay_opacity,
            'active'         => (bool) $t->active,
            'backgroundUrl'  => $t->background_url
                ? (filter_var($t->background_url, FILTER_VALIDATE_URL)
                    ? $t->background_url
                    : Storage::url($t->background_url))
                : null,
            'preview_url'    => $t->preview_url ? Storage::url($t->preview_url) : null,
        ];
    }

    private function saveDataUrl(string $dataUrl, string $folder): ?string
    {
        if (!str_starts_with($dataUrl, 'data:image/')) return null;
        $data     = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $filename = $folder . '/' . Str::uuid() . '.png';
        Storage::disk('public')->put($filename, base64_decode($data));
        return $filename;
    }
}
