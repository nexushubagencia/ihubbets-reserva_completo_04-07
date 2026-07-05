@extends('adminlte::page')

@section('title', 'Chaves do Cassino | IHUB BETS')

@section('content_header')
    <h1 class="text-dark font-weight-bold">
        <i class="fas fa-key text-primary mr-2"></i> Chaves do Cassino
        <small class="text-muted text-sm font-weight-normal">Credenciais dos provedores</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-lg border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h3 class="card-title text-lg font-weight-bold text-secondary">
                        <i class="fas fa-lock mr-2 text-primary"></i> Configurar Credenciais
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.casino.keys') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="font-weight-bold text-primary mb-3">PlayFiver / Fivers</h5>
                                <div class="form-group">
                                    <label>Agent Code</label>
                                    <input type="text" name="agent_code" class="form-control" value="{{ old('agent_code', $keys?->agent_code) }}">
                                </div>
                                <div class="form-group">
                                    <label>Agent Token</label>
                                    <input type="text" name="agent_token" class="form-control" value="{{ old('agent_token', $keys?->agent_token) }}">
                                </div>
                                <div class="form-group">
                                    <label>Agent Secret Key</label>
                                    <input type="text" name="agent_secret_key" class="form-control" value="{{ old('agent_secret_key', $keys?->agent_secret_key) }}">
                                </div>
                                <div class="form-group">
                                    <label>API Endpoint</label>
                                    <input type="text" name="api_endpoint" class="form-control" value="{{ old('api_endpoint', $keys?->api_endpoint) }}" placeholder="https://...">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="font-weight-bold text-primary mb-3">WorldSlot</h5>
                                <div class="form-group">
                                    <label>Agent Code</label>
                                    <input type="text" name="worldslot_agent_code" class="form-control" value="{{ old('worldslot_agent_code', $keys?->worldslot_agent_code) }}">
                                </div>
                                <div class="form-group">
                                    <label>Agent Token</label>
                                    <input type="text" name="worldslot_agent_token" class="form-control" value="{{ old('worldslot_agent_token', $keys?->worldslot_agent_token) }}">
                                </div>
                                <div class="form-group">
                                    <label>Agent Secret Key</label>
                                    <input type="text" name="worldslot_agent_secret_key" class="form-control" value="{{ old('worldslot_agent_secret_key', $keys?->worldslot_agent_secret_key) }}">
                                </div>
                                <div class="form-group">
                                    <label>API Endpoint</label>
                                    <input type="text" name="worldslot_api_endpoint" class="form-control" value="{{ old('worldslot_api_endpoint', $keys?->worldslot_api_endpoint) }}">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="font-weight-bold text-primary mb-3">VeniX</h5>
                                <div class="form-group">
                                    <label>Agent Code</label>
                                    <input type="text" name="venix_agent_code" class="form-control" value="{{ old('venix_agent_code', $keys?->venix_agent_code) }}">
                                </div>
                                <div class="form-group">
                                    <label>Agent Token</label>
                                    <input type="text" name="venix_agent_token" class="form-control" value="{{ old('venix_agent_token', $keys?->venix_agent_token) }}">
                                </div>
                                <div class="form-group">
                                    <label>Agent Secret</label>
                                    <input type="text" name="venix_agent_secret" class="form-control" value="{{ old('venix_agent_secret', $keys?->venix_agent_secret) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="font-weight-bold text-primary mb-3">MaxApiGames</h5>
                                <div class="form-group">
                                    <label>Agent Code</label>
                                    <input type="text" name="maxapigames_agent_code" class="form-control" value="{{ old('maxapigames_agent_code', $keys?->maxapigames_agent_code) }}">
                                </div>
                                <div class="form-group">
                                    <label>Agent Token</label>
                                    <input type="text" name="maxapigames_agent_token" class="form-control" value="{{ old('maxapigames_agent_token', $keys?->maxapigames_agent_token) }}">
                                </div>
                                <div class="form-group">
                                    <label>Agent Secret</label>
                                    <input type="text" name="maxapigames_agent_secret" class="form-control" value="{{ old('maxapigames_agent_secret', $keys?->maxapigames_agent_secret) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button type="submit" class="btn btn-primary font-weight-bold">
                            <i class="fas fa-save mr-2"></i> Salvar Credenciais
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
