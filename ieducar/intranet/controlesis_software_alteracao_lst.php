<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	*																	     *
	*	@author Prefeitura Municipal de Itaja�								 *
	*	@updated 29/03/2007													 *
	*   Pacote: i-PLB Software P�blico Livre e Brasileiro					 *
	*																		 *
	*	Copyright (C) 2006	PMI - Prefeitura Municipal de Itaja�			 *
	*						ctima@itajai.sc.gov.br					    	 *
	*																		 *
	*	Este  programa  �  software livre, voc� pode redistribu�-lo e/ou	 *
	*	modific�-lo sob os termos da Licen�a P�blica Geral GNU, conforme	 *
	*	publicada pela Free  Software  Foundation,  tanto  a vers�o 2 da	 *
	*	Licen�a   como  (a  seu  crit�rio)  qualquer  vers�o  mais  nova.	 *
	*																		 *
	*	Este programa  � distribu�do na expectativa de ser �til, mas SEM	 *
	*	QUALQUER GARANTIA. Sem mesmo a garantia impl�cita de COMERCIALI-	 *
	*	ZA��O  ou  de ADEQUA��O A QUALQUER PROP�SITO EM PARTICULAR. Con-	 *
	*	sulte  a  Licen�a  P�blica  Geral  GNU para obter mais detalhes.	 *
	*																		 *
	*	Voc�  deve  ter  recebido uma c�pia da Licen�a P�blica Geral GNU	 *
	*	junto  com  este  programa. Se n�o, escreva para a Free Software	 *
	*	Foundation,  Inc.,  59  Temple  Place,  Suite  330,  Boston,  MA	 *
	*	02111-1307, USA.													 *
	*																		 *
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
require_once ("include/clsBase.inc.php");
require_once ("include/clsListagem.inc.php");
require_once ("include/clsBanco.inc.php");
require_once( "include/pmicontrolesis/geral.inc.php" );

class clsIndexBase extends clsBase
{
	function Formular()
	{
		$this->SetTitulo( "Prefeitura de Itaja&iacute; - Listagem de Altera&ccedil;&atilde;o de Software" );
		$this->processoAp = "794";
	}
}

class indice extends clsListagem
{
	/**
	 * Referencia pega da session para o idpes do usuario atual
	 *
	 * @var int
	 */
	var $__pessoa_logada;

	/**
	 * Titulo no topo da pagina
	 *
	 * @var int
	 */
	var $__titulo;

	/**
	 * Quantidade de registros a ser apresentada em cada pagina
	 *
	 * @var int
	 */
	var $__limite;

	/**
	 * Inicio dos registros a serem exibidos (limit)
	 *
	 * @var int
	 */
	var $__offset;

	var $cod_software_alteracao;
	var $ref_funcionario_exc;
	var $ref_funcionario_cad;
	var $ref_cod_software;
	var $motivo;
	var $tipo;
	var $descricao;
	var $data_cadastro;
	var $data_exclusao;
	var $ativo;

	function Gerar()
	{
		@session_start();
		$this->__pessoa_logada = $_SESSION['id_pessoa'];
		session_write_close();

		$this->__titulo = "Software Alteracao - Listagem";

		foreach( $_GET AS $var => $val ) // passa todos os valores obtidos no GET para atributos do objeto
			$this->$var = ( $val === "" ) ? null: $val;

		$this->addBanner( "http://ieducar.dccobra.com.br/intranet/imagens/nvp_top_intranet.jpg", "http://ieducar.dccobra.com.br/intranet/imagens/nvp_vert_intranet.jpg", "Intranet" );

		$this->addCabecalhos( array(
			//"Software Alterac&atilde;o",
			"Software",
			"Motivo",
			"Tipo",
			"Descric&atilde;o"
		) );

		// Filtros de Foreign Keys
		$opcoes = array( "" => "Selecione" );
		if( class_exists( "clsPmicontrolesisSoftware" ) )
		{
			$objTemp = new clsPmicontrolesisSoftware();
			$lista = $objTemp->lista();
			if ( is_array( $lista ) && count( $lista ) )
			{
				foreach ( $lista as $registro )
				{
					$opcoes["{$registro['cod_software']}"] = "{$registro['nm_software']}";
				}
			}
		}
		else
		{
			echo "<!--\nErro\nClasse clsPmicontrolesisSoftware nao encontrada\n-->";
			$opcoes = array( "" => "Erro na geracao" );
		}
		$this->campoLista( "ref_cod_software", "Software", $opcoes, $this->ref_cod_software );



		// outros Filtros
		$this->campoLista( "motivo", "Motivo", array('' => 'Selecione','i' => 'Inser��o','a' => 'Altera��o','e' => 'Exclus�o'), $this->motivo );

		$this->campoLista( "tipo", "Tipo", array('' => 'Selecione','s' => 'Script','b' => 'Banco'), $this->tipo );


		// Paginador
		$this->__limite = 20;
		$this->__offset = ( $_GET["pagina_{$this->nome}"] ) ? $_GET["pagina_{$this->nome}"]*$this->__limite-$this->__limite: 0;

		$obj_software_alteracao = new clsPmicontrolesisSoftwareAlteracao();
		$obj_software_alteracao->setOrderby( "motivo ASC" );
		$obj_software_alteracao->setLimite( $this->__limite, $this->__offset );

		$lista = $obj_software_alteracao->lista(
			null,
			null,
			$this->ref_cod_software,
			$this->motivo,
			$this->tipo,
			$this->descricao,
			null,
			null,
			1
		);

		$total = $obj_software_alteracao->_total;

		// monta a lista
		if( is_array( $lista ) && count( $lista ) )
		{
			foreach ( $lista AS $registro )
			{
				// muda os campos data
				$registro["data_cadastro_time"] = strtotime( substr( $registro["data_cadastro"], 0, 16 ) );
				$registro["data_cadastro_br"] = date( "d/m/Y H:i", $registro["data_cadastro_time"] );

				$registro["data_exclusao_time"] = strtotime( substr( $registro["data_exclusao"], 0, 16 ) );
				$registro["data_exclusao_br"] = date( "d/m/Y H:i", $registro["data_exclusao_time"] );


				// pega detalhes de foreign_keys
				if( class_exists( "clsFuncionario" ) )
				{
					$obj_ref_funcionario_exc = new clsFuncionario( $registro["ref_funcionario_exc"] );
					$det_ref_funcionario_exc = $obj_ref_funcionario_exc->detalhe();
					if( is_object( $det_ref_funcionario_exc["idpes"] ) )
					{
						$det_ref_funcionario_exc = $det_ref_funcionario_exc["idpes"]->detalhe();
						$registro["ref_funcionario_exc"] = $det_ref_funcionario_exc["nome"];
					}
					else
					{
						$pessoa = new clsPessoa_( $det_ref_funcionario_exc["idpes"] );
						$det_ref_funcionario_exc = $pessoa->detalhe();
						$registro["ref_funcionario_exc"] = $det_ref_funcionario_exc["nome"];
					}
				}
				else
				{
					$registro["ref_funcionario_exc"] = "Erro na geracao";
					echo "<!--\nErro\nClasse nao existente: clsFuncionario\n-->";
				}

				if( class_exists( "clsFuncionario" ) )
				{
					$obj_ref_funcionario_cad = new clsFuncionario( $registro["ref_funcionario_cad"] );
					$det_ref_funcionario_cad = $obj_ref_funcionario_cad->detalhe();
					if( is_object( $det_ref_funcionario_cad["idpes"] ) )
					{
						$det_ref_funcionario_cad = $det_ref_funcionario_cad["idpes"]->detalhe();
						$registro["ref_funcionario_cad"] = $det_ref_funcionario_cad["nome"];
					}
					else
					{
						$pessoa = new clsPessoa_( $det_ref_funcionario_cad["idpes"] );
						$det_ref_funcionario_cad = $pessoa->detalhe();
						$registro["ref_funcionario_cad"] = $det_ref_funcionario_cad["nome"];
					}
				}
				else
				{
					$registro["ref_funcionario_cad"] = "Erro na geracao";
					echo "<!--\nErro\nClasse nao existente: clsFuncionario\n-->";
				}

				if( class_exists( "clsPmicontrolesisSoftware" ) )
				{
					$obj_ref_cod_software = new clsPmicontrolesisSoftware( $registro["ref_cod_software"] );
					$det_ref_cod_software = $obj_ref_cod_software->detalhe();
					$registro["ref_cod_software"] = $det_ref_cod_software["nm_software"];
				}
				else
				{
					$registro["ref_cod_software"] = "Erro na geracao";
					echo "<!--\nErro\nClasse nao existente: clsPmicontrolesisSoftware\n-->";
				}

				$opcoes = array('i' => 'Inser��o','a' => 'Altera��o','e' => 'Exclus�o');
				$registro["motivo"] = $opcoes[$registro["motivo"]];

				$opcoes = array('s' => 'Script','b' => 'Banco');
				$registro["tipo"] = $opcoes[$registro["tipo"]];

				$this->addLinhas( array(
					//"<a href=\"controlesis_software_alteracao_det.php?cod_software_alteracao={$registro["cod_software_alteracao"]}\">{$registro["cod_software_alteracao"]}</a>",
					"<a href=\"controlesis_software_alteracao_det.php?cod_software_alteracao={$registro["cod_software_alteracao"]}\">{$registro["ref_cod_software"]}</a>",
					"<a href=\"controlesis_software_alteracao_det.php?cod_software_alteracao={$registro["cod_software_alteracao"]}\">{$registro["motivo"]}</a>",
					"<a href=\"controlesis_software_alteracao_det.php?cod_software_alteracao={$registro["cod_software_alteracao"]}\">{$registro["tipo"]}</a>",
					"<a href=\"controlesis_software_alteracao_det.php?cod_software_alteracao={$registro["cod_software_alteracao"]}\">" . truncate( $registro["descricao"], 30 ) . "</a>"
				) );
			}
		}
		$this->addPaginador2( "controlesis_software_alteracao_lst.php", $total, $_GET, $this->nome, $this->__limite );

		$this->acao = "go(\"controlesis_software_alteracao_cad.php\")";
		$this->nome_acao = "Novo";

		$this->largura = "100%";
	}
}
// cria uma extensao da classe base
$pagina = new clsIndexBase();
// cria o conteudo
$miolo = new indice();
// adiciona o conteudo na clsBase
$pagina->addForm( $miolo );
// gera o html
$pagina->MakeAll();
?>
