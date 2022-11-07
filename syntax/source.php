<?php
/**
 * DokuWiki Plugin randomtable (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Oscar Merida <oscar@oscarm.org>
 */
class syntax_plugin_randomtables_source  extends \dokuwiki\Extension\SyntaxPlugin
{
    /** @inheritDoc */
    public function getType(): string
    {
        return 'protected';
    }

    /** @inheritDoc */
    public function getPType(): string
    {
        return 'block';
    }

    /** @inheritDoc */
    public function getSort(): int
    {
        return 200;
    }

    /** @inheritDoc */
    public function connectTo($mode): void
    {
        $this->Lexer->addEntryPattern('\<RANDOMTABLE\s+[A-Za-z0-9_]+>', $mode, 'plugin_randomtables_source');
    }

    /** @inheritDoc */
    public function postConnect(): void
    {
        $this->Lexer->addExitPattern('</RANDOMTABLE>', 'plugin_randomtables_source');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler): array
    {
		static $tableID;

     	switch ($state) {
			case DOKU_LEXER_ENTER:
				preg_match('/\s([A-Za-z0-9_]+)\>$/', $match, $parts);
				$tableID[] = $parts[1];

				return [$state, $match, $parts[1]];

			case DOKU_LEXER_UNMATCHED:
				$match = preg_split("/(\r|\n|\r\n)/m", $match);

				$match = $this->parseLines($match);

				if (!$_GET['do']) {
					$data = [
						'lines' => $match,
						'id' => array_pop($tableID),
					];
					trigger_event('randomtables_save', $data);
				}

				return [$state, $match];

			case DOKU_LEXER_EXIT:
				return [$state, ''];

		}
        return [];
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data): bool
    {
        if ($mode !== 'xhtml') {
            return false;
        }		

        [$state, $match] = $data;

		switch ($state) {
			case DOKU_LEXER_ENTER:
				$id = $data[2];
				$renderer->doc .= '<div class="randomtable-well"><button class="randomtable" data-src="' . $id . '" data-target="results-' 
					           . $id . '">Roll</button><div id="results-' . $id .'" class="results"></div></div>' . PHP_EOL;
				$renderer->doc .= '<table id="' . $id.'" class="inline table table-striped table-condensed randomtable">' . PHP_EOL;
				$renderer->doc .='<thead><tr><th>Range</th><th>Result</th></tr></thead>' . PHP_EOL;
				break;

			case DOKU_LEXER_UNMATCHED:

				foreach ($match as $line) {
					[$min, $max, $txt] = $line;

					$range = ($min === $max) ? $min : $min . '-' . $max;
					
					$renderer->doc .= '<tr>'
						. '<td>' . $range . '</td>'
						. '<td>' . $renderer->_xmlEntities($txt) .'</td>'
						. '</tr>' . PHP_EOL; 
				}

				break;

			case DOKU_LEXER_EXIT:
				$renderer->doc .= "</table>" . PHP_EOL;
				break;
		}

        return true;
    }

	private function parseLines(array $match): array
	{
		$match = array_filter($match);

		$match = array_map(function($line) use (&$count) {
			if (preg_match('/^(\d+)\.*(?:[ -])+(\d+)\.*\s*(.+)$/', $line, $subm)) {
				$count = (int) $subm[2];
				return [(int) $subm[1], (int) $subm[2], $subm[3]];
			} 

			if (preg_match('/^(\d+)\.*\s+(.+)$/', $line, $subm)) {
				$count = (int) $subm[1];
				return [(int) $subm[1], (int) $subm[1], $subm[2]];
			} 

			$count++;
			return [$count, $count,  $line];
	

		}, $match);

		return $match;
	}
}
