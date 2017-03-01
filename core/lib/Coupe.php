<?php

final class Coupe
{

	private static $inst;

	private $currentContent;

	private $currentTemplate;

	private $config = array();

	private $vars = array();

	/**
	 * Armazena o adaptardor
	 * com as funções de recuperação
	 * dos arquivos.
	 */
	private $adapter;

	/**
	 * Define um adaptador que irá
	 * fazer a recuperação dos
	 * arquivos.
	 */
	public function setAdapter(\Coupe\Adapter $adp)
	{

		$this->adapter = $adp;
		
		return true;

	}

	public static function & getInstance()
	{
		
		if( ! self::$inst )
		{
			self::$inst = new Coupe();
		}

		return self::$inst;

	}
	
	public function exec()
	{
		
		// pega as configuracoes do programa
		$this->loadConfig();
		
		// pega a url passada e prepara dizendo qual eh o conteudo e os GETs
		$this->clearDocumentMethod();
		
		// importa algumas classes que sao usadas no nucleo o programa
		if ($this->config['ACTIVE_DEBUG']==TRUE) $this->import('Debug');
		if ($this->config['ACTIVE_LOG']==TRUE) $this->import('Log');
		$this->import('File');
		
		// ******* DEBUG *********
		$this->debug('group', array('COMPONENTS', array('cupe_content', 'cupe_template', 'cupe_blocks', 'cupe_modules', 'cupe_classes')));
		$this->debug('description', array('cupe_blocks', 'Blocks called'));
		$this->debug('description', array('cupe_content', 'Actual Content'));
		$this->debug('description', array('cupe_classes', 'Loaded Classes'));
		$this->debug('description', array('cupe_template', 'Actual Template'));
		$this->debug('description', array('cupe_modules', 'Modules called'));
		$this->debug('group', array('PHP COUPÉ', array('coupe')));
		$this->debug('timer', array('coupe', 'start', '', 'Total script time'));
		// ******* DEBUG /********
		
		// carrega a pagina
		$page = $this->loadPage();
		
		// ******* DEBUG *********
		$this->debug('timer', array('coupe', 'stop'));
		if (class_exists('Debug')) echo $this->Debug->show();
		// ******* DEBUG /********

		echo $page;
	}

	public function getConfig($k)
	{
		return $this->config[$k];
	}
	
	// pega as variaveis de configuracao do arquivo de configuracao e coloca em um array do objeto Coupe
	// recebe: VOID
	// retorna: VOID
	private function loadConfig() {
		// carregar arquivo de configuracao do PHP Coupe
		@require(APP_FOLDER."config.php");
		// passa as variaveis de configuracao para o PHP Coupe
		foreach ($config as $key=>$value)
		{
			$this->config[$key] = $value;
			// se a configuracao for de diretorio, coloca o diretorio 'manager' como parte do caminho
			if (substr($key, 0, 3)=='DIR')
			{
				$this->config[$key] = APP_FOLDER.$value;
			}
		}
		// retira, se houver no arquivo de configuracao, a barra no final do dominio do projeto
		$this->config['BASE_PATH'] = trim($this->config['BASE_PATH'], '/\\');
	}
	
	// funcao especifica para tratar as URL amigaveis
	// recebe: VOID
	// retorna: VOID (atribui variaveis que informam ao programa o conteudo atual e GETs passados)
	function clearDocumentMethod()
	{
		if ($this->config['URL_FRIENDLY']==TRUE)
		{
			$url = trim($_SERVER['REQUEST_URI'], '/\\');
			// se estiver usando URLs amigaveis, mas nao estiver usando htaccess, tiramos a palavra 'index.php' para tratar a URL
			// coloquei IF pois se a pagina fosse a inicial (sem ter o 'index.php' na URL), ele cortava a URI assim mesmo e falhava 
			if ($this->config['URL_HTACCESS']==FALSE)
			{
				$url = str_replace('index.php/', '', $url);
			}
			$url = explode('/', $url);
			if ($url[0] != '')
			{
				$size = sizeof($url);
				for ($x=0; $x<$size; $x++)
				{
					if (strpos($url[$x], ':'))
					{
						$params = explode(':', $url[$x]);
						$_GET[$params[0]] = $params[1];
						unset($url[$x]);
					}
				}
				// TRATAR ISSO NO HTACCESS
				// esse problema eh causado usando o htaccess e o projeto em uma pasta interna do servidor
				// isso por que o programa pensa que a / da pasta, seja um id de documento e tenta acha-lo
				if ($sublevels = substr_count($this->config['BASE_PATH'], '/'))
				{
					if (strstr($this->config['BASE_PATH'], '://'))
					{
						$sublevels = $sublevels - 2;
					}
					for ($x=0; $x<$sublevels; $x++)
					{
						array_shift($url);
					}
				}
				// TRATAR ISSO NO HTACCESS
				$id = implode('.', $url);
				if ($id == '')
				{
					$id = $this->config['START_PAGE'];
				}
				if (file_exists($this->config['DIR_CONTENT'].$id.'.cnt.php'))
				{
					$_GET['id'] = $id;
				}
				else
				{
					$_GET['id'] = '404';
				}
			}
		}
	}
	
	// funcao para incluir classes dentro dos modulos
	// recebe: ($string=nome da classe)
	// retorna: VOID (atribuicao de uma classe)
	// TODO: isso aqui é para importar as classes. Mas está importando objetos.
	function import($classes, $param=array())
	{
		$list = explode(',', $classes);
		// so se pode passar parametros para iniciar a classe se for indicada uma classe na chamada
		// caso contrario o FOR abaixo irah usar o parametro para todas as classes que ele for criar
		if (sizeof($list)>1) $param=FALSE;
		foreach ($list as $class_name)
		{
			$class_name = trim($class_name);
			$file = is_file(APP_FOLDER.'lib/'.$class_name.'.php');
			if ($file)
			{
				if (!class_exists($class_name)) {
					require_once(APP_FOLDER.'lib/'.$class_name.'.php');
					$this->$class_name = new $class_name($param);
					// ******* DEBUG *********
					$this->debug('mensage', array('cupe_classes', $class_name));
					// ******* DEBUG /********
				}
			}
		}
	}
	
	// registra eventos ocorridos dentro do programa em um arquivo de log
	// recebe: ($string='mensagem')
	// retorna : STRING (resultado do metodo) ou FALSE
	function log($mensage, $prefix='')
	{
		if ($this->config['ACTIVE_LOG']==TRUE)
		{
			return $this->Log->write($mensage, $prefix);
		}	
	}
	
	// invoca os metodos da classe result se o debug estiver ativo
	// recebe: ($string='metodo', $string='parametros')
	// returna : STRING (resultado do metodo) ou FALSE
	function debug($function, $values=FALSE)
	{
		if ($this->config['ACTIVE_DEBUG']==TRUE)
		{
			switch($function)
			{
				case 'timer' : return $this->Debug->$function($values[0], $values[1], @$values[2], @$values[3]);
				case 'mensage' : return $this->Debug->$function($values[0], $values[1], @$values[2]);
				case 'group' : return $this->Debug->$function($values[0], $values[1]);
				case 'description' : return $this->Debug->$function($values[0], $values[1]);
				case 'show' : return $this->Debug->$function();
				default : return FALSE;
			}
		}	
	}

	// funcao para carregar a pagina final
	// recebe:
	// retorna:
	private function loadPage()
	{
		// se tiver sido postado para mudar a linguagem, seta um cookie
		if (isset($_POST['phpcoupe_translate']) || isset($_GET['phpcoupe_translate']))
		{
			$idiom = (isset($_POST['phpcoupe_translate'])) ? $_POST['phpcoupe_translate'] : $_GET['phpcoupe_translate'];
			// confere se o que foi postado estah no array de idiomas das configuracoes
			if (array_key_exists($idiom, $this->config['IDIOM']))
			{
				setcookie('phpcoupe_translate', $idiom, time()+60*60*24*5, '/');
			}
		}
		$page = '';
		// pega url solicitada por get ou retorna a pagina inicial
		// if (!isset($this->currentContent)) $this->currentContent = $this->config['START_PAGE'];
		isset($_GET['id']) ? $this->currentContent = $_GET['id'] : $this->currentContent = $this->config['START_PAGE'];
		// se o cache estiver habilitado tenta pegar o arquivo na pasta cache
		if ($this->config['ACTIVE_CACHE']==TRUE)
		{
			$page = $this->File->read($this->config['DIR_CACHE'].$this->currentContent.$this->config['EXT_CACHE']);
		}
		// ******* DEBUG *********
		$this->debug('mensage', array('cupe_content', $this->currentContent));
		// ******* DEBUG /********
		if ($page==FALSE)
		{
			// pega o template
			$tmp_data = $this->getTemplate($this->currentContent);
			// resolve todas as tags da template
			$page = $this->resolveTags($tmp_data['body']);
			if ($this->config['ACTIVE_CACHE']==TRUE) $this->File->write($page, $this->config['DIR_CACHE'].$this->currentContent.$this->config['EXT_CACHE']);
		}
		$page = $this->getNocacheModule($page);
		$page = $this->bindVars($page);
		return $page;
	}
	
	// funcao para resolver tudo que estah dentro de entre {{}}
	// recebe: ($string=conteudo_de_um_arquivo)
	// retorna: STRING (conteudo do arquivo resolvidas as chamadas entre '{{}}')
	function getApis($page)
	{

		$m = self::listTags($page, '~\{\{(.*?)\}\}~');

		// para cada chamada encontrada, executar essas operacoes
		for($i=0; $i<count($m); $i++)
		{

			$html = false;
			
			switch ($m[$i])
			{

				case 'content':

					$html = $this->adapter->getContent($this->currentContent);

					break;

			}
			
			// ******* DEBUG *********
			if ($this->config['ACTIVE_DEBUG']==TRUE) $html = '<div style="border: 1px solid green; overflow: auto; position: relative"><span style="color: #fff; background: green; position: absolute; top:0px; left: 0px;">API '.$matches[$i].'</span>'.$html.'</div>';
			// ******* DEBUG /********

			$page = str_replace('{{'.$m[$i].'}}', $html, $page);
		
		}

		return $page;
	
	}

	// detecta o metodo usado para requisitar o documento: urls amigaveis ou GET
	/* AINDA NAO IMPLEMENTADO */
	// recebe: VOID
	// retorna: STRING (identificacao do metodo usado na URL)
	function getDocumentMethod()
	{

		if(isset($_REQUEST['phpcoupe_url']))
		{
			return "alias";
		}
		elseif(isset($_REQUEST['id']))
		{
			return "id";
		}
		else
		{
			return "none";
		}

	}
	
	// funcao para pegar o idioma atual do site ou o selecionado atraves da marcacao {{translate}}
	// essa funcao soh foi criada para tirar o retardo do COOKIE
	// assim, pegamos o valor de POST ao inves de COOKIE por que cookies soh podem ser lidos na proxima pagina
	// recebe: VOID
	// retorna: 
	function getIdiom()
	{

		if ( isset($_POST['phpcoupe_translate']) ) $idiom = $_POST['phpcoupe_translate'];

		elseif ( isset($_GET['phpcoupe_translate']) ) $idiom = $_GET['phpcoupe_translate'];

		elseif ( isset($_COOKIE['phpcoupe_translate']) ) $idiom = $_COOKIE['phpcoupe_translate'];

		else $idiom = $this->config['IDIOM_DEFAULT'];

		return $idiom;

	}
	
	/*
	 * Carrega o template para
	 * o conteúdo indicado.
	 */
	function getTemplate($id)
	{

		/*
		 * Primeiro verifica se existe
		 * algum template forcado para
		 * o identificador do conteúdo
		 * indicado...
		 */
		if( isset($this->config['TEMPLATE_FORCE'][$id]) ) 
		{
			$id = $this->config['TEMPLATE_FORCE'][$id]; 
		}

		/*
		 * Tenta carregar o template.
		 */
		try
		{
			$tmp_data = $this->adapter->getTemplate($id); 
		}
		catch(\Exception $e)
		{

			$this->import('Content');

			$parent = $this->Content->getParent($id, false);

			if( $parent )
			{
				return $this->getTemplate($parent);
			}
			else
			{

				try
				{
					$tmp_data = $this->adapter->getTemplate('default');
				}
				catch(\Exception $e)
				{
					$tmp_data = array('id'=>'', 'body'=>'{{content}}');
				}

			}

		}

		$this->currentTemplate = $tmp_data['id'];

		$this->debug('mensage', array('cupe_template', $this->currentTemplate));

		$this->log("Template {$id} carregado");

		return $tmp_data;

	}
	
	/**
	 * Carrega o arquivo de conteúdo
	 * especificado.
	 */
	function getContent($id)
	{

		try
		{

			$content = $this->adapter->getDocument($id);

		}
		catch (Exception $e)
		{

			$this->log("O conteúdo $id não foi encontrado.");

			try
			{

				$id = '404';

				$content = $this->adapter->getDocument($id);

			}
			catch (Exception $e)
			{

				$this->log("Não há arquivo de conteúdo para ser carregado.");

				$content = "Deu pau!";

			}

		}

		$this->log("Conteúdo $id carregado");
	
		$content = $this->resolveTags($content);
	
		return $content;

	}
	
	// pega o conteudo de determinado bloco de conteudo
	// recebe o nome do bloco
	// recebe:
	// retorna:
	function getBlock($body)
	{

		// declara variavel onde ficarao os blocks encontrados
		$block = array();

		// busca tudo que estiver entre [# #]
		$m = self::listTags($body, '~\[\#(.*?)\#\]~');

		// para cada chamada encontrada, executar essas operacoes
		for($i=0; $i<count($m); $i++)
		{

			// ******* DEBUG *********
			$this->debug('mensage', array('cupe_blocks', $m[$i]));
			// ******* DEBUG /********

			// ********* LOG *********
			$this->log("Bloco $m[$i] carregado");
			// ********* LOG /********

			// pega o nome do bloco chamado
			$block['name'] = $m[$i];
			
			// pega o conteudo do arquivo de bloco chamado
			$block['code'] = $this->adapter->getBlock($block['name']); // $this->getDocument($this->config['DIR_BLOCK'], $block['name'].$this->config['EXT_BLOCK']);
			
			// se o bloco foi encontrado, resolve suas tags e substitui a marcacao no codigo
			if ($block['code'] != false)
			{			
				// resolve qualquer tag que por ventura exista no codigo do bloco
				$block['code'] = $this->resolveTags($block['code']);
				// ******* DEBUG *********
				if ($this->config['ACTIVE_DEBUG']==true) $block['code'] = '<div style="border: 1px solid blue; overflow: auto; position: relative"><span style="color: #fff; background: blue; position: absolute; top:0px; left: 0px;">'.$block['name'].'</span>'.$block['code'].'</div>';
				// ******* DEBUG /********
				// substitui no html a chamada do bloco pelo seu conteudo
				$body = str_replace("[#".$block['name']."#]", $block['code'], $body);

			}

		}

		// retorna o conteudo do documento jah com os blocos e modulos aplicados [pela funcao 'getContet']
		return $body;

	}
	
	// funcao para pegar os modulos
	// recebe (html, pegar modulos de cache?)
	// recebe um parametro informando se eh para pegar os modulos que serao salvos em cache
	// recebe:
	// retorna:
	function getModule($documentSource) {
		// declara variavel onde ficarao os codigos dos modulos e os modulos executados
		$module = array();
		$moduleExecuted = array();
		$parameter = null;
		// busca tudo que estiver entre [! !] (modulos que sao salvos em cache)
		$matches = self::listTags($documentSource, '~\[\!(.*?)\!\]~');
		// conta o numero de chamadas encontradas no codigo do documento
		$matchCount=count($matches);
		// para cada chamada encontrada, executar essas operacoes
		for($i=0; $i<$matchCount; $i++) {
			// ******* DEBUG *********
			$this->debug('timer', array('cupe_modules', 'start',  substr($matches[$i], 0, 35).'...'));
			// ******* DEBUG /********
			// ********* LOG *********
			$this->log("Módulo $matches[$i] carregado");
			// ********* LOG /********
			// se TIVER ? na declaracao eh pq tem paramentros declarados
			$spos = strpos($matches[$i], '?', 0);
			if($spos!==false) {
				// pega a declaracao de parametros a partir da posicao de ? ateh o final da variavel com os paramentros (strlen)
				$parameters = substr($matches[$i], $spos, strlen($matches[$i]));
			} else {
				// se NAO tiver ? na declaracao do modulo declara $parametros como vazio
				$parameters = '';
			}
			// como as declaracoes jah estao em uma variavel propria, limpa as declaracoes nas chamadas de modulos
			// substituindo na ocorrencia corrente o mesmo valor do parametro por vazio fica soh o nome
			//[modulo?a=1&b=2] fica agora como [[modulo]]
			$module[$i]['name'] = str_replace($parameters, '', $matches[$i]);
			$module[$i]['parameters'] = $parameters;
			$module[$i]['code'] = $this->adapter->getModule($module[$i]['name']); // $this->getDocument($this->config['DIR_MODULE'], $module[$i]['name'].$this->config['EXT_MODULE']);
			$module[$i]['code'] = $this->resolveTags($module[$i]['code']);
			// se o codigo for diferente de falso eh pq o arquivo do modulo chamado foi encontrato
			// entao, continuar o tratamento e retornar o documento com o modulo aplicado
			if($module[$i]['code'] != false) {
				// se a variavel com os codigos recebeu algum valor e nao esta vazia
				if(!empty($module[$i]['parameters'])) {
					// retira ? da linha e coloca numa variavel de uso temporario para fazer a operacao
					$parametersTemp = str_replace("?", "", $module[$i]['parameters']);
					// verifica se existe &amp; dentro do modulo. caso SIM coloca &amp; na variavel. caso NAO coloca &
					$divider = strpos($parametersTemp, "&amp;") > 0 ? "&amp;" : "&";
					// variavel temporaria se transforma em vetor com tadas atribuicoes do parametro passado
					$parametersTemp = explode($divider, $parametersTemp);
					// de acordo com a quatidade parametros dentro do vetor temporario...
					for($y=0; $y<count($parametersTemp); $y++) {
						// explode a variavel temporaria separando nome de variavel da atribuicao e valor
						// valor 2 da funcao explode indica que eh para ser retornado apenas 2 elementos para o vetor $parametroTemporario
						$parametroTemporario = explode("=", $parametersTemp[$y], 2);
						// alimenta o vetor parametro (declarado lah em cima da funcao) com a posicao com nome do parametro e seu valor
						$parameter[$parametroTemporario[0]] = $parametroTemporario[1];
					}
				}
				// retira as tags php do codigo (caso haja)
				$patterns = array('(\<\?php)', '(\<\?)', '(\?\>)');
				$module[$i]['code'] = preg_replace($patterns, '', $module[$i]['code']);
				// envia codigo da funcao carregada no momento pelo FOR para ser decodificada
				// pois seu conteudo vem do banco de dados codificado
				$moduleExecuted[$i] = $this->evalModule($module[$i]['code'], $parameter);
				$parameter = FALSE;
				// ******* DEBUG *********
				if ($this->config['ACTIVE_DEBUG']==TRUE) $moduleExecuted[$i] = '<div style="border: 1px solid red; overflow: auto; position: relative"><span style="color: #fff; background: red; position: absolute; top:0px; left: 0px;">'.$module[$i]['name'].'</span>'.$moduleExecuted[$i].'</div>';
				// ******* DEBUG /********
				// substitui a chamada no codigo do documento pelo codigo do modulo
				$documentSource = str_replace("[!".$module[$i]['name'].$module[$i]['parameters']."!]", $moduleExecuted[$i], $documentSource);
			}
			$this->debug('timer', array('cupe_modules', 'stop'));
		}
		// retorna o conteudo do documento jah com os modulos aplicados
		return $documentSource;
	}
		
	// funcao para decodificar o modulo pego do banco de dados
	// recebe: ($string=codigo_modulo, [$array=parametros_do_modulo])
	// retorna: STRING (resultado do modulo processado)
	function evalModule($code, $params)
	{

		$Coupe = $this;

		if( is_array($params) )
		{
			extract($params, EXTR_SKIP);
		}

		return eval($code);

	}
	
	// recebe:
	// retorna:
	function getNocacheModule ($body)
	{

		if(strpos($body, '[[')!==false)
		{
			$body = str_replace('[[', '[!', $body);
			$body = str_replace(']]', '!]', $body);
			$body = $this->getModule($body);
		}
		
		return $body;

	}
	
	/*
	 * Funcao de pegar conteudo
	 * do arquivo especificado
	 * com ou sem os modulos tratados
	 * pela funcao getModule()
	 */
	function resolveTags($body)
	{
		
		$body = $this->getApis($body);
		
		$body = $this->getBlock($body);
		
		$body = $this->getModule($body);
		
		$body = $this->getNocacheModule($body);
		
		return $body;

	}
	
	/**
	 * Função que pega tudo que estah
	 * entre o padrao (pattern) definido
	 * o padrao deve ser uma expressao
	 * regular.
	 *
	 * @param string $s String onde os padrões devem ser encontrados.
	 * @param string $regex Expressão regular com o padrão desejado.
	 * @return array Lista com os padrões encontrados.
	 */
	private static function listTags($s, $regex)
	{

		/**
		 * Pega tudo que for definido
		 * pelo padrão passado.
		 */
		preg_match_all($regex, $s, $matches);
		
		return $matches[1];
	
	}
	
	// seta variaveis globais que sao inseridas no lugar das marcacoes [%%]
	// recebe: ($string=nome_da_variavel, $string=valor_da_variavel)
	// retorna: VOID (apenas seta o conteudo da variavel em um array global da classe)
	function setVar ($name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	// substituis as marcacoes '[%nome_variavel%]' pelo seu conteudo armazenado no array global '$vars[]'
	// recebe: ($string=conteudo_retornado)
	// retorna: STRING (conteudo da pagina com as marcacoes substituidas por suas variaveis)
	protected function bindVars($body)
	{

		$m = self::listTags($body, '~\[\%(.*?)\%\]~');

		for($i=0; $i<count($m); $i++)
		{
			// ******* DEBUG *********
			// if ($this->config['ACTIVE_DEBUG']==TRUE) $block['code'] = '<div style="border: 1px solid blue; overflow: auto; position: relative"><span style="color: #fff; background: blue; position: absolute; top:0px; left: 0px;">'.$block['name'].'</span>'.$block['code'].'</div>';
			// ******* DEBUG /********
			$body = str_replace("[%" . $m[$i] . "%]", @$this->vars[ $m[$i] ], $body);
		}

		return $body;
	
	}

}

?>