<?php
/**
 * DokuWiki Plugin randomtable roller (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Oscar Merida <oscar@oscarm.org>
 */
class syntax_plugin_randomtables_roller  extends \dokuwiki\Extension\SyntaxPlugin
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
        return 201;
    }

    /** @inheritDoc */
	public function connectTo($mode): void
    {
        $this->Lexer->addSpecialPattern('<ROLL_ON[^>]+>', $mode, 'plugin_randomtables_roller');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Doku_Handler $handler): array
    {
		preg_match('/\s([A-Za-z0-9_]+)\>$/', $match, $parts);
		return [$state, $match, $parts[1]];
    }

    /** @inheritDoc */
    public function render($mode, Doku_Renderer $renderer, $data): bool
    {
        if ($mode !== 'xhtml') {
            return false;
        }		

		[$state, $match, $id] = $data;

		$renderer->doc .= '<div class="randomtable-well"><button class="randomtable" data-src="' . $id . '" data-target="results-' 
					   . $id . '">Roll</button><div id="results-' . $id .'" class="results"></div></div>' . PHP_EOL;

        return true;
    }
}
