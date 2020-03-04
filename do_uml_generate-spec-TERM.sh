rm -f docs/UML/classes/*.*
rm -f docs/UML/diagrams/*.*

../specifications-AA_GLOBAL/bin/uml_generate.sh -d svg -i {term_release} -r TERM -o docs/UML computable/UML/openEHR_UML-TERM.mdzip
