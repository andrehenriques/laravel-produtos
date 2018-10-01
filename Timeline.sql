select * from (
	select
		p.produto_id, p.produto_descricao,
		'Recebimento' as operacao, rp.recebimento_produto_quantidade as quatidade, r.recebimento_datahora as dh
	from tbl_produtos p
	inner join tbl_recebimentos_produtos rp on rp.produto_id = p.produto_id
	inner join tbl_recebimentos r on r.recebimento_id = rp.recebimento_id
	where p.produto_id = 2125

	union

	select
		p.produto_id, p.produto_descricao,
		'Saída' as operacao, sp.saida_produto_quantidade as quatidade, s.saida_datahora as dh
	from tbl_produtos p
	inner join tbl_saidas_produtos sp on sp.produto_id = p.produto_id
	inner join tbl_saidas s on s.saida_id = sp.saida_id
	where p.produto_id = 2125

	union

	select
		p.produto_id, p.produto_descricao,
        IF(ae.ajuste_tipo = 'E', 'Ajuste de entrada', 'Ajuste de entrada') as operacao,
        ae.ajuste_quantidade as quatidade, ae.ajuste_datahora as dh
	from tbl_produtos p
	inner join tbl_ajustes_estoque ae on ae.produto_id = p.produto_id
	where p.produto_id = 2125
) u
order by dh asc 

