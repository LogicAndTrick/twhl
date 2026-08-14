import {
    Parser,
    ParserConfiguration,
    INodeProcessor,
    INode,
    UnprocessablePlainTextNode,
    PlainTextNode,
    ParseData,
    HtmlNode,
    SmiliesProcessor,
} from '@logicandtrick/twhl-wikicode-parser';

const config = ParserConfiguration.Twhl();

if (import.meta.env.DEV) {
    const sp = config.Processors.find((x) => x instanceof SmiliesProcessor);
    if (sp) sp.UrlFormatString = 'http://twhl/images/smilies/{0}.png';
}

class LineMarkersProcessor implements INodeProcessor {
    Priority: number;

    constructor() {
        this.Priority = -999;
    }

    ShouldProcess(node: INode, _scope: string) {
        return node instanceof UnprocessablePlainTextNode || node instanceof PlainTextNode;
    }

    Process(_parser: Parser, _data: ParseData, node: PlainTextNode | UnprocessablePlainTextNode, _scope: string) {
        let text = node.Text;

        if (node instanceof UnprocessablePlainTextNode) {
            // just remove the line markers
            // oxlint-disable-next-line no-control-regex
            text = text.replace(/\x02(\d+)\x03/gim, '');
            node.Text = text;
            return [node];
        }

        const ret = [];
        const matches = [];

        // oxlint-disable-next-line no-control-regex
        const regex = /\x02(\d+)\x03/gim;
        let match = regex.exec(text);
        while (match != null) {
            matches.push(match);
            match = regex.exec(text);
        }

        let start = 0;
        for (const mat of matches) {
            if (mat.index < start) continue;
            if (mat.index > start) ret.push(new PlainTextNode(text.substring(start, mat.index)));

            const marker = mat[1];
            ret.push(new HtmlNode(`<a id="position-${marker}" data-position="${marker}"></a>`, PlainTextNode.Empty(), ''));

            start = mat.index + mat[0].length;
        }
        if (start < text.length) ret.push(new PlainTextNode(text.substring(start)));

        return ret;
    }
}

config.Processors.push(new LineMarkersProcessor());

export const parser = new Parser(config);
