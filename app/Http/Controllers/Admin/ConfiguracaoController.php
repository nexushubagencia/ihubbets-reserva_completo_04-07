<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Configuracao;
use App\Models\ListleaguesMain;
use App\Models\MainLeague;
use App\Models\User;
use App\Models\Site;


class ConfiguracaoController extends Controller
{
    public function indexView()
    {
        return view('admin.configuracao');
    }

    public function financeiroGatewaysView()
    {
        return view('admin.financeiro-gateways');
    }

    public function financeiroPixUsuariosView()
    {
        $siteId = app('tenant.site_id');
        $usuarios = User::where('site_id', $siteId)
            ->whereIn('role', ['manager', 'seller', 'user'])
            ->orderBy('role')
            ->get();

        return view('admin.financeiro-pix-usuarios', compact('usuarios'));
    }

    public function userFinancialUpdate(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
        }

        $user->update([
            'balance'      => $request->balance,
            'pix_key_type' => $request->pix_key_type,
            'pix_key'      => $request->pix_key,
            'cpf'          => $request->cpf
        ]);

        return response()->json(['success' => true, 'message' => 'Dados do usuário atualizados com sucesso!']);
    }

    public function index()
    {
        $siteId = app('tenant.site_id');
        $config = Configuracao::where('site_id', $siteId)->get();
        $site = Site::find($siteId);
        
        if ($config->isNotEmpty() && $site) {
            $config[0]->mercadopago_access_token = $site->pix_client_secret;
            $config[0]->social_instagram = $site->social_instagram;
            $config[0]->social_facebook = $site->social_facebook;
            $config[0]->social_twitter = $site->social_twitter;
            $config[0]->social_youtube = $site->social_youtube;
            $config[0]->whatsapp_number = $site->whatsapp_number;
            $config[0]->about_us = $site->about_us;
            $config[0]->op_futebol = $site->op_futebol;
            $config[0]->op_quininha = $site->op_quininha;
            $config[0]->op_seninha = $site->op_seninha;
            $config[0]->op_basquete = $site->op_basquete;
            $config[0]->op_tenis = $site->op_tenis;
            $config[0]->op_volei = $site->op_volei;
            $config[0]->op_ufcbox = $site->op_ufcbox;
        }
        
        return $config;
    }

    public function update(Request $request, $id)
    {
        try {
            $configuracao = Configuracao::find($id);
            if (!$configuracao) {
                return response()->json(['success' => false, 'message' => 'Configuração não encontrada.'], 404);
            }

            $data = $request->data[0];

            $siteFields = [
                'pix_client_secret' => $data['mercadopago_access_token'] ?? null,
                'social_instagram' => $data['social_instagram'] ?? null,
                'social_facebook' => $data['social_facebook'] ?? null,
                'social_twitter' => $data['social_twitter'] ?? null,
                'social_youtube' => $data['social_youtube'] ?? null,
                'whatsapp_number' => $data['whatsapp_number'] ?? null,
                'about_us' => $data['about_us'] ?? null,
                'op_futebol' => $data['op_futebol'] ?? null,
                'op_quininha' => $data['op_quininha'] ?? null,
                'op_seninha' => $data['op_seninha'] ?? null,
                'op_basquete' => $data['op_basquete'] ?? null,
                'op_tenis' => $data['op_tenis'] ?? null,
                'op_volei' => $data['op_volei'] ?? null,
                'op_ufcbox' => $data['op_ufcbox'] ?? null,
            ];

            // Remove from configuracao payload to avoid column not found in 'configuracaos' table
            $fieldsToRemove = [
                'mercadopago_access_token', 'social_instagram', 'social_facebook', 
                'social_twitter', 'social_youtube', 'whatsapp_number', 'about_us'
            ];

            foreach ($fieldsToRemove as $f) {
                if (isset($data[$f])) unset($data[$f]);
            }

            // Garante que campos de 'op_' sejam mantidos se existirem no payload
            $configuracao->update($data);

            $siteId = app('tenant.site_id');
            Site::where('id', $siteId)->update(array_filter($siteFields, function($val) {
                return $val !== null;
            }));

            return response()->json(['success' => true, 'message' => 'Configurações atualizadas com sucesso!']);
        } catch (\Exception $e) {
            \Log::error("Erro ao atualizar configurações: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    public function gerenciarCotacoes()
    {
        return view('admin.mercados-users');
    }

    public function gerenciarLigas()
    {
        return view('admin.bloqueio-ligas');
    }

    public function gerenciarMatchs()
    {
        return view('admin.bloqueio-matchs');
    }

    public function bloquearUser(Request $request)
    {
        $user = User::find($request->id);
        if ($user) {
            if ($request->has('situacao')) {
                $user->status = ($request->situacao == 'ativo') ? 1 : 0;
            } else {
                $user->status = $request->status;
            }
            $user->save();

            $cambistas = User::where('gerente_id', $user->id)->get();
            foreach ($cambistas as $cambista) {
                $cambista->status = $user->status;
                $cambista->save();
            }
        }
        return response()->json(['success' => true]);
    }

    public function showLigas()
    {
        $siteId = app('tenant.site_id');

        $mainleagues = MainLeague::where('site_id', $siteId)->get();

        $liga = array();
        foreach ($mainleagues as $league) {
            $liga[] = $league->league;
        }

        return ListleaguesMain::orderBy('league', 'ASC')
            ->whereNotIn('league', $liga)
            ->get();
    }

    public function ligasPrincipaisView()
    {
        return view('admin.ligas-principais');
    }


    public function deleteLeague($id)
    {
        $liga = MainLeague::find($id);
        $liga->delete();
    }

    public function uploadLogo(Request $request)
    {
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg'])) {
                $file->move(public_path('img'), 'logo.png');
                return response()->json(['message' => 'Logo atualizada com sucesso!']);
            }
            return response()->json(['message' => 'Formato de imagem inválido. Use PNG, JPG ou JPEG.'], 400);
        }
        return response()->json(['message' => 'Nenhum arquivo enviado.'], 400);
    }

    public function uploadFavicon(Request $request)
    {
        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $extension = $file->getClientOriginalExtension();
            if (in_array(strtolower($extension), ['png', 'jpg', 'jpeg', 'ico'])) {
                $file->move(public_path('img'), 'favicon.png');
                return response()->json(['message' => 'Favicon atualizado com sucesso!']);
            }
            return response()->json(['message' => 'Formato de imagem inválido. Use PNG, JPG, JPEG ou ICO.'], 400);
        }
        return response()->json(['message' => 'Nenhum arquivo enviado.'], 400);
    }
}
