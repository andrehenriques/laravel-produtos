select p.*, (
	(
		(select sum(rp.recebimento_produto_quantidade) from tbl_recebimentos_produtos rp where rp.produto_id = p.produto_id)+
		(select sum(ae.ajuste_quantidade) from tbl_ajustes_estoque ae where ae.produto_id = p.produto_id and ae.ajuste_tipo = 'E')
    )-
    ((select sum(ps.saida_produto_quantidade) from tbl_saidas_produtos ps where ps.produto_id = p.produto_id)
    +
    (select sum(ae.ajuste_quantidade) from tbl_ajustes_estoque ae where ae.produto_id = p.produto_id and ae.ajuste_tipo = 'S'))
) as saldo
from tbl_produtos p;