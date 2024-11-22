document.addEventListener("DOMContentLoaded", function() {
            const moduleStart = document.getElementById("moduleStart");
            const moduleGoal = document.getElementById("moduleGoal");
            const showPriceReg = document.getElementById("showPriceReg");

            const createOptionsFromObj = (arr, sel) => {
                // Liste zurücksetzen
                sel.innerHTML = '';
                if (arr) {
                    arr.forEach(module => {
                        const option = document.createElement("option");
                        option.value = module.value;
                        option.textContent = module.name;
                        sel.appendChild(option);
                    });
                }
            };
            // Initial beide Dropdowns mit allen Optionen befüllen
            createOptionsFromObj(acfCourseData.moduleDataCourses, moduleStart);
            createOptionsFromObj(acfCourseData.moduleDataCourses, moduleGoal);

            // Aktualisieren von `moduleGoal`, basierend auf dem Status in `moduleStart`
            const updateCourseOptions = () => {
                const startIndex = moduleStart.selectedIndex;

                if (startIndex !== -1) {
                    // Filtere die Optionen ab dem nächsten Index
                    const filteredOptions = acfCourseData.moduleDataCourses.slice(startIndex + 1);

                    // Aktualisiere die Liste für `moduleGoal`
                    createOptionsFromObj(filteredOptions, moduleGoal);
                }
            }

            const updatePrice = () => {
                const startIndex = moduleStart.selectedIndex;
                const goalIndex = moduleGoal.selectedIndex;

                if (startIndex !== -1 && goalIndex !== -1) {
                    // Ermitteln der Preise zwischen den Indizes (inklusive `moduleStart`, exklusiv `moduleGoal`)
                    const pricesInBetween = acfCourseData.moduleDataCourses
                        .slice(startIndex, startIndex + goalIndex + 1) // Zielbereich
                        .map(module => parseFloat(module.value)); // Preise extrahieren und zu Zahlen konvertieren

                    // Summe berechnen
                    const totalPrice = pricesInBetween.reduce((sum, price) => sum + price, 0);

                    // Ausgabe in der Konsole
                    console.log("Preise zwischen Auswahl:", pricesInBetween);
                    console.log("Gesamtsumme:", totalPrice);
                }
            }

            updateCourseOptions();
            updatePrice();

            moduleStart.addEventListener('change', () => updateCourseOptions());
            moduleGoal.addEventListener('change', () => updatePrice());


        });