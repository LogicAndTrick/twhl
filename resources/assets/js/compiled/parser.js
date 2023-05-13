const wcp = require('@logicandtrick/twhl-wikicode-parser');
const config = wcp.ParserConfiguration.Twhl();

class LineMarkersProcessor {
    /** @field Priority number */
    constructor() {
        this.Priority = -999;
    }

    /**
     * @param node INode
     * @param scope string
     * @return boolean
     */
    ShouldProcess(node, scope) {
        return node instanceof wcp.Nodes.UnprocessablePlainTextNode
            || node instanceof wcp.Nodes.PlainTextNode;

    }

    /**
     * @param parser Parser
     * @param data ParseData
     * @param node PlainTextNode | UnprocessablePlainTextNode
     * @param scope string
     * @return INode[]
     */
    Process(parser, data, node, scope) {
        /** @var text string */
        let text = node.Text;

        if (node instanceof wcp.Nodes.UnprocessablePlainTextNode) {
            // just remove the line markers
            text = text.replace(/\x02(\d+)\x03/img, '');
            node.Text = text;
            return [node];
        }

        const ret = [];
        const matches = [];

        const regex = /\x02(\d+)\x03/img;
        let match = regex.exec(text);
        while (match != null) {
            matches.push(match);
            match = regex.exec(text);
        }

        let start = 0;
        for (const mat of matches) {
            if (mat.index < start) continue;
            if (mat.index > start) ret.push(new wcp.Nodes.PlainTextNode(text.substring(start, mat.index)));

            const marker = mat[1];
            ret.push(new wcp.Nodes.HtmlNode(`<a id="position-${marker}" data-position="${marker}"></a>`, wcp.Nodes.PlainTextNode.Empty(), ''));

            start = mat.index + mat[0].length;
        }
        if (start < text.length) ret.push(new wcp.Nodes.PlainTextNode(text.substring(start)));

        return ret;
    }
}

config.Processors.push(new LineMarkersProcessor());

window.parser = new wcp.Parser(config);
