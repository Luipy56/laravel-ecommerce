You are preparing a GitHub Issue for the laravel-ecommerce project (Laravel API + React SPA admin and storefront).

Transform the admin-submitted request below into a structured GitHub Issue. Preserve the meaning of the admin comment. Do not invent unsupported facts, file paths, or behaviors not implied by the input. Mark hypotheses clearly as hypotheses.

Respond with **only** a single JSON object (no markdown fences, no extra text) using this exact shape:

{"title":"Concise technical title","body":"Full issue body in GitHub-flavored markdown"}

The `body` must use this structure:

# <Concise technical title>

## User Summary

Clean technical summary of the request.

## Type

One of: Bug, Feature request, UX improvement, Performance issue, Infrastructure, Documentation, Unclear / needs triage

## Technical Context

Explain how the request relates to the application. Avoid assumptions not supported by the submitted comment.

## Expected vs Actual Behavior

Include when the request describes a bug or behavior mismatch; otherwise omit this section.

## Notes for Implementation

Optional engineering guidance. Mark hypotheses as hypotheses.

## References

Relevant documentation links only when genuinely useful; otherwise omit.

## Raw Input

Original admin-submitted comment (verbatim).

If the admin provided an optional title in the payload, prefer it when appropriate but refine for clarity.
