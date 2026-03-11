@extends('reports.pdf.layout')

@section('title', 'Comissão Redeconomia')

@section('content')
@foreach ($dadosPorEmpresa as $loopIndex => $dados)
    @php $linha0 = $dados->first(); @endphp

    <div style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">

        <header>
            <table width="100%">
                <tr>
                    <td width="20%" align="left">
                        <img src="{{ public_path('img/logo_jagua.png') }}" style="height: 40px;">
                    </td>
                    <td width="60%" align="center">
                        <h2>COMISSÃO - RELATÓRIO PARA SIMPLES CONFERÊNCIA</h2>
                    </td>
                    <td width="20%" align="right">
                        <div>
                            <span class="page-number"></span><br>
                            {{ now()->format('d/m/Y H:i:s') }}
                        </div>
                    </td>
                </tr>
                <tr><td class="borda-divisao-dois" colspan="3"></td></tr>
            </table>

            <table width="100%">
                <tr>
                    <td colspan="3">
                        <h3>{{ $linha0->ep }} - {{ $linha0->den_empresa }}</h3>
                    </td>
                </tr>
                <tr>
                    <td align="left">
                        <h3>
                            <span style="font-weight: normal;">Representante / Vendedor:</span>
                            {{ $linha0->raz_social ?? 'REDECONOMIA' }}
                        </h3>
                    </td>
                    <td></td>
                    <td align="right">
                        <strong>Período:</strong>
                        {{ \Carbon\Carbon::parse($filtros['data_ini'])->format('d/m/Y') }}
                        a
                        {{ \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') }}
                    </td>
                </tr>
                <tr height="30px"><th></th></tr>
            </table>
        </header>

        <footer>
            <div class="borda-footer" align="center">DEMONSTRATIVO PARA SIMPLES CONFERÊNCIA</div>
        </footer>

        <div style="margin-top: 10px;">
            @include('reports.pdf.financeiro._tabela-comissao', [
                'dados' => $dados,
                'linha0' => $linha0,
            ])
        </div>

    </div>
@endforeach
@endsection
