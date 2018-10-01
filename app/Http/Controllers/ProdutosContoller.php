<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProdutosContoller extends Controller
{
    ///saldo_estoque
    public function saldoEstoque(Request $request)
    {
        $saldos = DB::select("select p.produto_id codigo, p.produto_descricao as descricao, (
            (
                (select sum(rp.recebimento_produto_quantidade) from tbl_recebimentos_produtos rp where rp.produto_id = p.produto_id)+
                (select sum(ae.ajuste_quantidade) from tbl_ajustes_estoque ae where ae.produto_id = p.produto_id and ae.ajuste_tipo = 'E')
            )-
            ((select sum(ps.saida_produto_quantidade) from tbl_saidas_produtos ps where ps.produto_id = p.produto_id)
            +
            (select sum(ae.ajuste_quantidade) from tbl_ajustes_estoque ae where ae.produto_id = p.produto_id and ae.ajuste_tipo = 'S'))
        ) as saldo
        from tbl_produtos p;");

        return response(['produtos'=>$saldos]);
    }

    ///rastreamento_produto
    public function rastreamentoProduto(Request $request)
    {
        $produto = $request->produto_id;

        if(empty($produto) || $produto == null) {
            return response()->json(null)->setStatusCode(400);
        }


        $rastreamento = DB::select("select * from (
            select
                p.produto_id, p.produto_descricao,
                'Recebimento' as operacao, rp.recebimento_produto_quantidade as quantidade, r.recebimento_datahora as dh
            from tbl_produtos p
            inner join tbl_recebimentos_produtos rp on rp.produto_id = p.produto_id
            inner join tbl_recebimentos r on r.recebimento_id = rp.recebimento_id
            where p.produto_id = ?
        
            union
        
            select
                p.produto_id, p.produto_descricao,
                'Saída' as operacao, sp.saida_produto_quantidade as quantidade, s.saida_datahora as dh
            from tbl_produtos p
            inner join tbl_saidas_produtos sp on sp.produto_id = p.produto_id
            inner join tbl_saidas s on s.saida_id = sp.saida_id
            where p.produto_id = ?
        
            union
        
            select
                p.produto_id, p.produto_descricao,
                IF(ae.ajuste_tipo = 'E', 'Ajuste de entrada', 'Ajuste de entrada') as operacao,
                ae.ajuste_quantidade as quantidade, ae.ajuste_datahora as dh
            from tbl_produtos p
            inner join tbl_ajustes_estoque ae on ae.produto_id = p.produto_id
            where p.produto_id = ?
        ) u
        order by dh asc 
        
        ", [$produto, $produto, $produto]);

        if(empty($rastreamento)) {
            return response()->json(null)->setStatusCode(404);
        }

        $movimentacoes = [];
        
        $descricao=null;
        foreach($rastreamento as $r) {
            if($descricao == null) {
                $descricao = $r->produto_descricao;
            }
            $movimentacao['movimentacao']=$r->operacao;
            $movimentacao['data_hora_movimentacao']=$r->dh;
            $movimentacao['quantidade_movimentada']=$r->quantidade;
            array_push($movimentacoes, $movimentacao);
        }

        $response = [
           'produto'=>[
                'codigo'=>$produto,
                'descricao'=>$descricao
           ],
           'movimentacoes'=>$movimentacoes
        ]; 
        return response($response);

    }
}
