# Rules

## To know

- Prefer new additive migrations for schema changes on long-lived (prod/stage) databases; never migrate:fresh / wipe / demo-seed those envs. See `.cursor/rules/project-standards.mdc` and `prod-db-no-destructive.mdc`.
- All comments must be in English; all strings that appear in the HTML must be in Catalan.
- Comments in English, field names in English, HTML in Catalan, enums in Catalan.
- The project is React + Laravel, but for now it only uses Laravel. No styling is to be done for now.
- Use a global styles.css file with variables as much as possible, but you can use a file for a style or for a component/page if it doesn't repeat and if it is necessary.