# AGENTS.md

Guidance for AI coding agents working in **specifications-TERM**.

## What this repo is

`specifications-TERM` is the **document + model source** for the openEHR **TERM** (openEHR Terminology) component. It is a *specification* repo, not software: the deliverables are the HTML specs at https://specifications.openehr.org/releases/TERM/. Sources are **AsciiDoc** prose, the terminology **XML** files under `computable/XML`, and the **BMM** schema `computable/BMM/openehr_term_3.1.0.bmm.json`; the committed `docs/*.html` files are build artefacts.

`manifest.json` is the source of truth for which documents exist, their `spec_status`, releases, and the `SPECTERM` Jira roadmap. Published documents: `SupportTerminology`, `en_openehr_terminology.xml`, `openehr_external_terminologies.xml`, `PropertyUnitData.xml`.

TERM is mostly data, not a class model. The XML files under `computable/XML` are the definitive expression of the openEHR terminology: the code sets and vocabularies that give values to coded attributes in the RM, AM and SM. The one document, `SupportTerminology`, is a documentary rendering of those files. The BMM and class tables cover only the small representation model (`TERMINOLOGY`, `CODE_SET`, `CODE`, `TERMINOLOGY_GROUP`, `TERMINOLOGY_CONCEPT`, `TERMINOLOGY_STATUS`), which the document says is not a normative model for internal terminology representation. The BMM is the only class-model source: it was extracted from a MagicDraw model in `6d026ba`, and that model has since been removed.

## Layout

- `docs/SupportTerminology/master.adoc` plus `masterNN-*.adoc` chapters (`master00` = amendment record); `manifest_vars.adoc` is generated from `manifest.json` on publish. `master05` and `master06` are stubs, commented out in `master.adoc`.
- `computable/BMM/openehr_term_3.1.0.bmm.json` — the BMM; source of truth for all classes. bmm-publisher's bundled copy and `specifications-ITS-BMM/components/TERM` are updated separately and can lag this file, so always pass this file by path.
- `docs/UML/classes/` — **generated** class tables, named `org.openehr.term.terminology.<class>.adoc` (hence `{pkg}` in chapter includes, and `-q` when publishing).
- `docs/UML/diagrams/TERM-term.terminology.svg` — the class diagram, drawn in MagicDraw; it is not produced by the class-table regeneration.
- `computable/XML/` — the hand-edited terminology sources, and `computable/FHIR/` — generated FHIR files; both are described under "Terminology data and generator".
- `development/` — the PHP generator that derives the generated files from the XML.
- `.asciidoctorconfig` — attributes for editor previews (`:component:`, `:imagesdir:`).

<!-- openehr-scaffold:begin plugin -->
## Use the `openehr-specs@openehr` plugin

The plugin carries the spec-authoring know-how — **prefer its skills/agents over ad-hoc edits.** Don't re-derive their workflows here. `.claude/settings.json` registers the `openehr` marketplace and enables the plugin; Claude Code asks you to trust the folder first. To install it by hand: `/plugin marketplace add openEHR/ai-plugins`, then `/plugin install openehr-specs@openehr`.

| Task | Use |
|------|-----|
| Create/edit a spec, chapter, `master.adoc`, or `manifest.json` | skill `openehr-specs:authoring` |
| Spec prose style — overviews, semantics, design rationale | skill `openehr-specs:content-patterns` |
| Add or change classes, attributes, functions or invariants in the BMM | skill `openehr-specs:bmm-authoring` |
| Regenerate class tables/diagrams from BMM (`bmm-publisher`) | skill `openehr-specs:class-generation` |
| Amendment record (`master00-amendment_record.adoc`) | skill `openehr-specs:amendment-record` |
| Releases, CR/PR, lifecycle status, Jira workflow | skill `openehr-specs:governance` |
| Quality / convention review of a document | skill `openehr-specs:review` |
| Whole-document convention review (all chapters) | agent `openehr-specs:spec-reviewer` |
| Fact-check class/attribute/function names in prose | agent `openehr-specs:identifier-grounding` |
| Audit `{openehr_*}` attributes + `<<anchor>>` cross-refs | agent `openehr-specs:xref-auditor` |
| Local HTML preview of this component (you run it) | `/openehr-specs:publish TERM` |
| Check this repo against the standard file set | `/openehr-specs:scaffold` |
<!-- openehr-scaffold:end plugin -->

<!-- openehr-scaffold:begin build -->
## Build tool invocation

Rendering the documents and regenerating the class tables need only Docker. All `specifications-XX` repos, including `specifications-AA_GLOBAL` (boilerplate, references), are cloned as siblings under one parent directory. The terminology generator in `development/` is separate; it is described in the next section. Render from the parent directory:

```bash
# render HTML. The image is published from specifications-AA_GLOBAL; its entrypoint passes -q
# (package-qualified class files). Use `Release-X.Y.Z` instead of `development` for a release build.
docker run --rm -u $(id -u):$(id -g) -v "$PWD:/documents/" ghcr.io/openehr/asciidoctor development TERM
```

Read the build log: any `ERROR` or `include file not found` line means incomplete output. An image built from AA_GLOBAL with `--failure-level=ERROR` then prints `FAILED <file>` and exits 1; an older image prints `generated <file>` and exits 0 regardless. The build also rewrites the tracked `docs/*.html` artefacts.

```bash
# regenerate class tables (NEVER hand-edit docs/UML/classes/*.adoc) — run from this repo's root.
# Pass the repo BMM by PATH: a bare schema id (openehr_term_3.1.0) uses the image's bundled copy, which lags this repo.
# The BMM lists BASE in `includes`, but bmm-publisher does not load included schemas itself. TERM's tables
# link to BASE types (String, List, Iso8601_date), so also pass the sibling BASE BMM as a dependency (-d);
# without it bmm-publisher warns "Type ... is not defined in any loaded schema" and those links come out as
# /classes/String instead of /releases/BASE/{base_release}/...
OUT=$(mktemp -d)
docker run --rm --user $(id -u):$(id -g) \
  -v "$PWD/computable/BMM/openehr_term_3.1.0.bmm.json":/in/openehr_term_3.1.0.bmm.json:ro \
  -v "$PWD/../specifications-BASE/computable/BMM/openehr_base_1.3.0.bmm.json":/in/openehr_base_1.3.0.bmm.json:ro \
  -v "$OUT":/out \
  ghcr.io/openehr/bmm-publisher legacy-adoc -d /in/openehr_base_1.3.0.bmm.json /in/openehr_term_3.1.0.bmm.json -o /out
# then diff "$OUT" against docs/UML/classes and copy over the tables you changed
```

Regenerated this way, the six tables match the committed ones byte for byte. Do not pass the schema id instead of the path (`legacy-adoc openehr_term_3.1.0 …`): bmm-publisher then exits 0 but silently renders its bundled copy of the BMM, not this repo's.

To change a class/attribute/function/invariant, edit the BMM schema and regenerate — never touch the generated tables (see skill `openehr-specs:class-generation`).
<!-- openehr-scaffold:end build -->

## Terminology data and generator

`development/` holds a PHP 8.4 generator, run in Docker, that derives several tracked files from the hand-edited XML.

**Edit by hand:**

- `computable/XML/<lang>/openehr_terminology.xml` — vocabularies (`<group>` of `<concept id rubric>`), one file per language (`en`, `es`, `ja`, `pt`, `zh`). `en` is the base the other languages are merged onto.
- `computable/XML/openehr_external_terminologies.xml` — code sets (`<codeset>` of `<code value description>`); no translations.
- `computable/XML/PropertyUnitData.xml` — UCUM properties and units for ADL tools; the generator does not read it.
- `computable/XML/schema/*.xsd` — the file formats; the generator does not validate against them.

**Generated, never hand-edit** (written by `generate_all`):

- `docs/SupportTerminology/codesets/*.adoc` — code set and vocabulary tables, included by `master03-terminology.adoc`.
- `computable/FHIR/codesystem-*.xml` and `valueset-*.xml` — one pair per openEHR vocabulary group.
- `computable/XML/*.v3.xml` — all languages merged into one file per terminology.
```bash
cd development
make install     # first time: build the PHP image, composer install
make generate    # same as: docker-compose run --rm php composer exec generate_all
make sh          # shell in the PHP container (composer and php are available there)
make help        # lists the actions; override the compose command with COMPOSE="docker compose"
```

Then review the changed generated files with `git diff`. `docker-compose.yml` mounts `../docs` and `../computable` into the container as `/data/docs` and `/data/computable`.

A change to terminology data touches, in order: the source XML (every language file for a vocabulary change), a generator run, an entry in `master00-amendment_record.adoc` (skill `openehr-specs:amendment-record`), and the release notes in `computable/XML/README.adoc`, which lists each change with its Jira key.

## Gotchas

- A new vocabulary group or code set needs hand-written attributes `:<openehr_id>_description:`, `:<openehr_id>_links:` (and `_ref:` for code sets) in `docs/SupportTerminology/terminology_vocabularies_vars.adoc` or `terminology_code_sets_vars.adoc`. The generated tables refer to them; without them the references stay unresolved.
- `generate_all` reports problems only as log lines (duplicate codes, codes missing from a translation, codes repeated across code sets) and does not fail on them, so read its output.
- `development/bin/generate_all` hard-codes the language list and the container paths under `/data/`. A new translation must be added to that list, and the script cannot run directly on the host.
- Each source XML carries a hand-edited `version` and `date`. The merged v3 file takes `version` from `en` and the newest `date` of all languages; `VERSION` in `development/src/constants.php` is only the fallback.
- `computable/FHIR` is upper-case on disk because the generator's `Writer` rewrites `fhir` to `FHIR` in output paths. The canonical FHIR URLs written into the files (`https://specifications.openehr.org/fhir/...`) stay lower-case.
- There are no tests, linter or CI config in this repo. Check a generator change by running it and reading the `git diff` of the generated files.
- BMM `documentation` strings pass through bmm-publisher's `formatText()`: `{attr}` is escaped, so write literal values (e.g. `latest`, not `{base_release}`); same-document `<<_x_class,X>>` xrefs work; use `×`, not `*`.

<!-- openehr-scaffold:begin conventions -->
## Conventions

### Commit messages

- **Format:** `Changes for <KEY> - <what changed>`, one line, for example `Changes for SPECTERM-42 - fix typos in the overview chapter`. Say what changed in the specification, not which file.
- **Several tickets:** join the numbers, `Changes for SPECTERM-49/50 - <what changed>`.
- **Every commit that has a Jira ticket carries its key.** Take it from, in order: the user, the branch name (`feat/SPECTERM-42-<slug>`), or the Jira issue the task links to (the `atlassian-openehr` MCP server can look an issue up). Never invent or guess a key.
- **Which key:** `SPECTERM` for this component (change requests), `SPECPR` for problem reports, `SPECPUB` for publishing and tooling issues, or the owning component's key (`SPECAM`, `SPECRM`, ...) when the change belongs there. See skill `openehr-specs:governance`.
- **No ticket:** write a plain summary without a key and say so in the pull request; do not use a placeholder key.
- Some history, mostly in other components, puts the key last, `<what changed> (SPECTERM-42)`. It is understood, but use the leading form here.
- A change to published specification text also needs an amendment-record entry with the same ticket (skill `openehr-specs:amendment-record`).
- Do not stage regenerated `docs/*.html` or generated class tables together with source changes unless the task is to refresh them.

### Branches

- Branch from `master` as `feat/<KEY>-<slug>` or `fix/<KEY>-<slug>`, for example `feat/SPECTERM-42-template-id`, and merge to `master` by pull request.
<!-- openehr-scaffold:end conventions -->
