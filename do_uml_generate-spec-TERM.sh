rm docs/UML/classes/*.*
rm docs/UML/diagrams/*.*

./uml_generate.sh -i {term_release} -r TERM -o docs/UML computable/UML/openEHR_UML-TERM.mdzip
