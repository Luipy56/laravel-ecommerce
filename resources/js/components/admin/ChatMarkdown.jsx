import React, { useMemo } from 'react';
import ReactMarkdown from 'react-markdown';

const mdComponents = {
  p: ({ children }) => <p className="mb-2 last:mb-0 leading-relaxed">{children}</p>,
  ul: ({ children }) => <ul className="mb-2 list-disc space-y-1 pl-5 last:mb-0">{children}</ul>,
  ol: ({ children }) => <ol className="mb-2 list-decimal space-y-1 pl-5 last:mb-0">{children}</ol>,
  li: ({ children }) => <li className="leading-relaxed">{children}</li>,
  strong: ({ children }) => <strong className="font-semibold">{children}</strong>,
  em: ({ children }) => <em className="italic">{children}</em>,
  a: ({ href, children }) => (
    <a href={href} className="link link-primary" target="_blank" rel="noopener noreferrer">{children}</a>
  ),
  code: ({ className, children, ...props }) => {
    const inline = !className;
    if (inline) {
      return <code className="rounded bg-base-300/60 px-1 py-0.5 font-mono text-[0.85em]" {...props}>{children}</code>;
    }
    return (
      <code className="mb-2 block overflow-x-auto whitespace-pre-wrap rounded-lg bg-base-300/60 p-2 font-mono text-xs" {...props}>
        {children}
      </code>
    );
  },
  pre: ({ children }) => <div className="mb-2">{children}</div>,
  h1: ({ children }) => <h3 className="mb-1 mt-2 text-base font-semibold first:mt-0">{children}</h3>,
  h2: ({ children }) => <h3 className="mb-1 mt-2 text-base font-semibold first:mt-0">{children}</h3>,
  h3: ({ children }) => <h4 className="mb-1 mt-2 text-sm font-semibold first:mt-0">{children}</h4>,
  hr: () => <hr className="my-2 border-base-300" />,
  blockquote: ({ children }) => (
    <blockquote className="my-2 border-l-4 border-primary/40 pl-2 italic opacity-90">{children}</blockquote>
  ),
};

/**
 * Split GFM pipe-tables from the rest so we can render tables without remark-gfm.
 * @returns {list<{type: 'md'|'table', text?: string, headers?: string[], rows?: string[][]}>}
 */
function splitBlocks(raw) {
  const lines = String(raw ?? '').replace(/\r\n/g, '\n').split('\n');
  const blocks = [];
  let buf = [];

  const flushMd = () => {
    if (buf.length === 0) return;
    blocks.push({ type: 'md', text: buf.join('\n') });
    buf = [];
  };

  const isSep = (line) => /^\s*\|?[\s:|-]+\|[\s:|-]*\|?\s*$/.test(line) && line.includes('-');
  const isRow = (line) => line.trim().startsWith('|') && line.trim().endsWith('|');

  const parseRow = (line) => line
    .trim()
    .replace(/^\|/, '')
    .replace(/\|$/, '')
    .split('|')
    .map((c) => c.trim());

  let i = 0;
  while (i < lines.length) {
    if (isRow(lines[i]) && i + 1 < lines.length && isSep(lines[i + 1])) {
      flushMd();
      const headers = parseRow(lines[i]);
      i += 2;
      const rows = [];
      while (i < lines.length && isRow(lines[i])) {
        rows.push(parseRow(lines[i]));
        i += 1;
      }
      blocks.push({ type: 'table', headers, rows });
      continue;
    }
    buf.push(lines[i]);
    i += 1;
  }
  flushMd();
  return blocks;
}

function MdTable({ headers, rows }) {
  return (
    <div className="mb-2 max-w-full overflow-x-auto last:mb-0">
      <table className="table table-xs w-full min-w-[12rem]">
        <thead>
          <tr>
            {headers.map((h, idx) => (
              <th key={`h-${idx}`} className="whitespace-nowrap">{h}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, rIdx) => (
            <tr key={`r-${rIdx}`}>
              {headers.map((_, cIdx) => (
                <td key={`c-${rIdx}-${cIdx}`} className="align-top">{row[cIdx] ?? ''}</td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default function ChatMarkdown({ content }) {
  const blocks = useMemo(() => splitBlocks(content), [content]);

  return (
    <div className="chat-md text-sm">
      {blocks.map((b, i) => {
        if (b.type === 'table') {
          return <MdTable key={`t-${i}`} headers={b.headers} rows={b.rows} />;
        }
        const text = (b.text || '').trim();
        if (!text) return null;
        return (
          <ReactMarkdown key={`m-${i}`} components={mdComponents}>
            {text}
          </ReactMarkdown>
        );
      })}
    </div>
  );
}
