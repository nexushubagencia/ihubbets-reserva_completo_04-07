<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManualEvent;
use App\Models\ManualCategory;
use Illuminate\Support\Facades\Storage;

class PersonalizedMatchesController extends Controller
{
    /**
     * Lista de Jogos Personalizados
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        
        $query = ManualEvent::with('category')->where('site_id', config('tenant.site_id', 1));

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('home_team', 'LIKE', "%{$search}%")
                  ->orWhere('away_team', 'LIKE', "%{$search}%")
                  ->orWhere('league_name', 'LIKE', "%{$search}%");
            });
        }

        $matches = $query->orderBy('start_time', 'desc')->paginate(15);
            
        $categories = ManualCategory::all();
        
        if ($categories->isEmpty()) {
            foreach(['Futebol', 'Vaquejada', 'Kings League', 'X1'] as $cat) {
                 ManualCategory::create(['name' => $cat]);
            }
            $categories = ManualCategory::all();
        }

        return view('admin.personalized.index', compact('matches', 'categories', 'search'));
    }

    /**
     * Store de novo jogo manual
     */
    public function store(Request $request)
    {
        $siteId = config('tenant.site_id', 1);

        // Permite esporte livre
        if ($request->filled('sport_name')) {
            $category = ManualCategory::firstOrCreate(['name' => $request->sport_name]);
            $categoryId = $category->id;
        } else {
            $categoryId = $request->category_id;
        }

        $match = new ManualEvent();
        $match->site_id = $siteId;
        $match->category_id = $categoryId;
        $match->title = $request->title ?: ($request->home_team . ' x ' . $request->away_team);
        $match->league_name = $request->league_name;
        $match->home_team = $request->home_team;
        $match->away_team = $request->away_team;
        $match->odd_home = $this->formatOdd($request->odd_home);
        $match->odd_draw = $this->formatOdd($request->odd_draw);
        $match->odd_away = $this->formatOdd($request->odd_away);
        
        $match->score = $request->score;
        
        // Mercados Dinâmicos (Grupos)
        $extraMarkets = [];
        if ($request->has('groups')) {
            foreach ($request->groups as $group) {
                if (!empty($group['title'])) {
                    $selections = [];
                    if (isset($group['selections'])) {
                        foreach ($group['selections'] as $sel) {
                            if (!empty($sel['name'])) {
                                $selections[] = [
                                    'name' => $sel['name'],
                                    'odd' => $this->formatOdd($sel['odd'] ?? 0)
                                ];
                            }
                        }
                    }
                    $extraMarkets[] = [
                        'group_name' => $group['title'],
                        'selections' => $selections
                    ];
                }
            }
        }
        $match->extra_markets = $extraMarkets;
        $match->is_featured = $request->has('is_featured');

        $match->start_time = $request->start_date . ' ' . $request->start_time_only;
        $match->status = 'open';

        // Upload de Escudo Mandante
        if ($request->hasFile('home_flag')) {
            $file = $request->file('home_flag');
            $filename = time() . '_home_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/flags'), $filename);
            $match->home_flag = 'uploads/flags/' . $filename;
        }

        if ($request->hasFile('away_flag')) {
            $file = $request->file('away_flag');
            $filename = time() . '_away_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/flags'), $filename);
            $match->away_flag = 'uploads/flags/' . $filename;
        }

        $match->save();

        // Gerenciar Destaque (Home)
        $this->syncFeatured($match);

        return redirect()->route('admin.personalized.index')->with('success', 'Partida personalizada criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $match = ManualEvent::findOrFail($id);
        
        if ($request->filled('sport_name')) {
            $category = ManualCategory::firstOrCreate(['name' => $request->sport_name]);
            $match->category_id = $category->id;
        } elseif ($request->filled('category_id')) {
            $match->category_id = $request->category_id;
        }
        
        $match->title = $request->title ?: ($request->home_team . ' x ' . $request->away_team);
        $match->league_name = $request->league_name;
        $match->home_team = $request->home_team;
        $match->away_team = $request->away_team;
        $match->odd_home = $this->formatOdd($request->odd_home);
        $match->odd_draw = $this->formatOdd($request->odd_draw);
        $match->odd_away = $this->formatOdd($request->odd_away);
        
        $match->score = $request->score;

        // Mercados Dinâmicos (Grupos)
        $extraMarkets = [];
        if ($request->has('groups')) {
            foreach ($request->groups as $group) {
                if (!empty($group['title'])) {
                    $selections = [];
                    if (isset($group['selections'])) {
                        foreach ($group['selections'] as $sel) {
                            if (!empty($sel['name'])) {
                                $selections[] = [
                                    'name' => $sel['name'],
                                    'odd' => $this->formatOdd($sel['odd'] ?? 0)
                                ];
                            }
                        }
                    }
                    $extraMarkets[] = [
                        'group_name' => $group['title'],
                        'selections' => $selections
                    ];
                }
            }
        }
        $match->extra_markets = $extraMarkets;
        $match->is_featured = $request->has('is_featured');
        
        $match->start_time = $request->start_date . ' ' . $request->start_time_only;

        if ($request->hasFile('home_flag')) {
            $file = $request->file('home_flag');
            $filename = time() . '_home_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/flags'), $filename);
            $match->home_flag = 'uploads/flags/' . $filename;
        }

        if ($request->hasFile('away_flag')) {
            $file = $request->file('away_flag');
            $filename = time() . '_away_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/flags'), $filename);
            $match->away_flag = 'uploads/flags/' . $filename;
        }

        $match->save();

        // Gerenciar Destaque (Home)
        $this->syncFeatured($match);

        return redirect()->route('admin.personalized.index')->with('success', 'Partida atualizada com sucesso!');
    }

    public function toggleStatus($id)
    {
        $match = ManualEvent::findOrFail($id);
        $match->status = $match->status == 'open' ? 'cancelled' : 'open';
        $match->save();

        return response()->json(['success' => true, 'new_status' => $match->status]);
    }

    /**
     * Deletar Partida
     */
    public function destroy($id)
    {
        $match = ManualEvent::findOrFail($id);
        
        // Remove dos destaques se estiver lá
        \DB::table('featured_matches')
            ->where('site_id', config('tenant.site_id', 1))
            ->where('manual_event_id', $id)
            ->where('is_manual', true)
            ->delete();

        $match->delete();
        return back()->with('success', 'Partida removida.');
    }

    /**
     * Sincroniza com a tabela featured_matches (Destaques da Home)
     */
    private function syncFeatured($match)
    {
        $siteId = config('tenant.site_id', 1);

        // Remove se existir
        \DB::table('featured_matches')
            ->where('site_id', $siteId)
            ->where('manual_event_id', $match->id)
            ->where('is_manual', true)
            ->delete();

        if ($match->is_featured) {
            \DB::table('featured_matches')->insert([
                'site_id' => $siteId,
                'is_manual' => true,
                'manual_event_id' => $match->id,
                'home_team' => $match->home_team,
                'away_team' => $match->away_team,
                'match_date' => $match->start_time,
                'sport' => $match->category->name ?? 'Futebol',
                'league_name' => $match->league_name,
                'badge_color' => $match->cor_badge ?? '#007bff',
                'background_path' => $match->img_featured,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Formata odd para decimal (troca vírgula por ponto)
     */
    private function formatOdd($value)
    {
        if (empty($value)) return 0;
        $val = str_replace(',', '.', $value);
        return (float) $val;
    }
}
