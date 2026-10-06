import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import { IconCollapse, IconExpand } from '../icons';

const PROVIDERS = ['ollama', 'cursor', 'heuristic'];
const STORAGE_PROVIDER = 'admin-chat-provider';
const STORAGE_EXPANDED = 'admin-chat-expanded';
const KEY_MARK = '/images/serraller_solidaria_chat_key.png';

function BrandKey({ className = 'h-8 w-8' }) {
  return (
    <span className={`inline-flex items-center justify-center overflow-hidden rounded-full bg-white ${className}`}>
      <img src={KEY_MARK} alt="" className="h-[86%] w-[86%] object-contain" />
    </span>
  );
}

export default function AdminChatWidget() {
  const { t } = useTranslation();
  const [open, setOpen] = useState(false);
  const [expanded, setExpanded] = useState(() => {
    try {
      return localStorage.getItem(STORAGE_EXPANDED) === '1';
    } catch {
      return false;
    }
  });
  const [provider, setProvider] = useState(() => {
    try {
      const v = localStorage.getItem(STORAGE_PROVIDER);
      return PROVIDERS.includes(v) ? v : 'ollama';
    } catch {
      return 'ollama';
    }
  });
  const [draft, setDraft] = useState('');
  const [busy, setBusy] = useState(false);
  const [messages, setMessages] = useState([]);
  const panelRef = useRef(null);
  const inputRef = useRef(null);

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_PROVIDER, provider);
    } catch {
      // ignore
    }
  }, [provider]);

  useEffect(() => {
    try {
      localStorage.setItem(STORAGE_EXPANDED, expanded ? '1' : '0');
    } catch {
      // ignore
    }
  }, [expanded]);

  useEffect(() => {
    if (!open) return undefined;
    inputRef.current?.focus();
    const onKey = (e) => {
      if (e.key === 'Escape') setOpen(false);
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [open]);

  const send = useCallback(async () => {
    const text = draft.trim();
    if (!text || busy) return;
    const history = messages.map((m) => ({ role: m.role, content: m.content }));
    setDraft('');
    setMessages((prev) => [...prev, { role: 'user', content: text }]);
    setBusy(true);
    try {
      const { data } = await api.post('/admin/chat', { message: text, provider, history }, { timeout: 90000 });
      const payload = data?.data || {};
      const reply = payload.reply || t('admin.chat.error');
      const downloads = Array.isArray(payload.downloads) ? payload.downloads : [];
      setMessages((prev) => [...prev, { role: 'assistant', content: reply, downloads, provider: payload.provider }]);
    } catch {
      setMessages((prev) => [...prev, { role: 'assistant', content: t('admin.chat.error') }]);
    } finally {
      setBusy(false);
    }
  }, [busy, draft, messages, provider, t]);

  const panelClass = expanded
    ? 'fixed bottom-6 right-6 z-[60] flex h-[min(85dvh,42rem)] w-[min(96vw,48rem)] flex-col overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-2xl'
    : 'fixed bottom-24 right-4 z-[60] flex h-[min(70dvh,28rem)] w-[min(100vw-1.5rem,22rem)] flex-col overflow-hidden rounded-2xl border border-base-200 bg-base-100 shadow-2xl sm:right-6';

  return (
    <>
      {open && (
        <section
          ref={panelRef}
          className={panelClass}
          aria-label={t('admin.chat.title')}
        >
          <header className="flex items-center gap-2 border-b border-base-200 bg-gradient-to-r from-primary to-secondary px-3 py-2 text-primary-content">
            <BrandKey className="h-8 w-8 shrink-0" />
            <div className="min-w-0 flex-1">
              <p className="truncate text-sm font-semibold leading-tight">{t('admin.chat.title')}</p>
              <p className="truncate text-[10px] opacity-80">{t('admin.chat.subtitle')}</p>
            </div>
            <button
              type="button"
              className="btn btn-ghost btn-xs text-primary-content"
              onClick={() => setExpanded((v) => !v)}
              aria-label={expanded ? t('admin.chat.collapse') : t('admin.chat.expand')}
              title={expanded ? t('admin.chat.collapse') : t('admin.chat.expand')}
            >
              {expanded ? <IconCollapse className="h-4 w-4" /> : <IconExpand className="h-4 w-4" />}
            </button>
            <button type="button" className="btn btn-ghost btn-xs text-primary-content" onClick={() => setOpen(false)} aria-label={t('common.close')}>
              ×
            </button>
          </header>
          <label className="flex items-center gap-2 border-b border-base-200 px-3 py-2 text-xs">
            <span className="text-base-content/60">{t('admin.chat.provider')}</span>
            <select
              className="select select-xs select-bordered flex-1"
              value={provider}
              onChange={(e) => setProvider(e.target.value)}
            >
              {PROVIDERS.map((p) => (
                <option key={p} value={p}>{t(`admin.chat.provider_${p}`)}</option>
              ))}
            </select>
          </label>
          <div className="flex min-h-0 flex-1 flex-col gap-2 overflow-y-auto px-3 py-2 text-sm">
            {messages.length === 0 && (
              <p className="text-base-content/60">{t('admin.chat.empty')}</p>
            )}
            {messages.map((m, i) => (
              <div key={`${m.role}-${i}`} className={m.role === 'user' ? 'self-end rounded-xl bg-primary/15 px-3 py-2' : 'self-start rounded-xl bg-base-200 px-3 py-2'}>
                <p className="whitespace-pre-wrap">{m.content}</p>
                {Array.isArray(m.downloads) && m.downloads.map((d) => (
                  <a key={d.token || d.url} className="link link-primary mt-1 block text-xs" href={d.url} target="_blank" rel="noreferrer">
                    {d.filename || t('admin.chat.download')}
                  </a>
                ))}
              </div>
            ))}
            {busy && <p className="text-xs text-base-content/50">{t('common.loading')}</p>}
          </div>
          <form
            className="flex gap-2 border-t border-base-200 p-2"
            onSubmit={(e) => {
              e.preventDefault();
              send();
            }}
          >
            <textarea
              ref={inputRef}
              className={`textarea textarea-bordered textarea-sm flex-1 ${expanded ? 'min-h-20' : 'min-h-12'}`}
              rows={expanded ? 4 : 2}
              value={draft}
              onChange={(e) => setDraft(e.target.value)}
              placeholder={t('admin.chat.placeholder')}
              maxLength={4000}
              onKeyDown={(e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                  e.preventDefault();
                  send();
                }
              }}
            />
            <button type="submit" className="btn btn-brand-gradient btn-sm self-end" disabled={busy || !draft.trim()}>
              {t('admin.chat.send')}
            </button>
          </form>
        </section>
      )}
      <button
        type="button"
        className="btn btn-circle fixed bottom-6 right-6 z-50 min-h-14 min-w-14 border-0 bg-gradient-to-br from-primary to-secondary p-0 text-primary-content shadow-lg shadow-primary/25 ring-1 ring-inset ring-white/15 hover:brightness-110"
        aria-label={open ? t('common.close') : t('admin.chat.open')}
        aria-expanded={open}
        onClick={() => setOpen((v) => !v)}
      >
        {open ? (
          <span className="text-2xl leading-none" aria-hidden="true">×</span>
        ) : (
          <BrandKey className="h-11 w-11" />
        )}
      </button>
    </>
  );
}
