# TERM development tool

This tool will parse openEHR terminology files located on /computable/XML folders and will generate:

* XML v3 files that combines all languages in one single file
* Asciidoctor files to be integrated with the other spec adoc files
* FHIR CodeSystem and ValueSet files based on discovered terminology vocabulary

## Usage

First you will need to change directory to `development`.
Docker-compose will be used to create a container with PHP code that will perform all necessary tasks.
The sequence of commands should normally be:

```bash
cd development
docker-compose run --rm php composer install
docker-compose run --rm php composer exec generate_all
```

An alternative is to use the provided `Makefile`, which wraps the same commands. To get the help on it you will need to execute:

```bash
make help
```

The sequence above then becomes `make install` followed by `make generate`.

## Output

Files are generated in `computable` and `docs/SupportTerminology/codesets` directory.
