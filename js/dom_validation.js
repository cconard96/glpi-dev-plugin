class GlpiDevDOMValidationTool {
    static VALIDATION_INTERVAL = 10000;

    static reported_elements = [];

    /**
     *
     * @type {{
     * new_failures: {message: string, element: Element}[]
     * }}
     */
    static validation_session_data = {};

    static start() {
        if (this.interval) {
            return;
        }

        this.validateDOM();
        this.interval = window.setInterval(() => {
            this.validateDOM();
        }, this.VALIDATION_INTERVAL);
    }

    static restart() {
        window.clearInterval(this.interval);
        this.start();
    }

    static validateDOM() {
        // Reset validation session data
        this.validation_session_data = {
            new_failures: [],
        };

        // Check for elements with duplicated IDs
        this.checkDuplicatedIDs();

        // Check for elements with a backslash in the ID, name or classes
        this.checkForBackslash();

        // Print session results
        this.printValidationSessionResults();

        // Add all new failed elements to the reported elements list
        this.validation_session_data.new_failures.forEach(failure => {
            this.reported_elements.push(failure.element);
        });
    }

    static checkDuplicatedIDs() {
        const elements = document.querySelectorAll('[id]');

        // Group elements by ID
        const grouped_elements = Array.from(elements).reduce((acc, element) => {
            const id = element.getAttribute('id');

            if (!acc[id]) {
                acc[id] = [];
            }

            acc[id].push(element);

            return acc;
        }, {});

        // Get all elements with duplicated IDs
        const duplicated_elements = Object.values(grouped_elements).filter(elements => elements.length > 1);

        // Report all elements with duplicated IDs
        duplicated_elements.forEach(elements => {
            elements.forEach(element => {
                this.reportElement(element, 'Duplicate ID');
            });
        });
    }

    static checkForBackslash() {
        // 4 backslashes used here as they should be already escaped in the DOM as \\ and each needs escaped again for the selector
        const backslash_in_id = $(`[id*="\\\\"]`);
        const backslash_in_name = $(`[name*="\\\\"]`);
        const backslash_in_class = $(`[class*="\\\\"]`);

        backslash_in_id.each((index, element) => {
            this.reportElement(element, 'Backslash in ID');
        });
        backslash_in_name.each((index, element) => {
            this.reportElement(element, 'Backslash in name');
        });
        backslash_in_class.each((index, element) => {
            this.reportElement(element, 'Backslash in class');
        });
    }

    static printValidationSessionResults() {
        let result = '';
        result += "DOM validation results:\n";
        result += `\tNew reported elements: ${this.validation_session_data.new_failures.length}\n`;
        result += `\tAlready reported elements: ${this.reported_elements.length}\n`;
        result += `\tNew failures:\n`;
        if (this.validation_session_data.new_failures.length > 0) {
            console.warn(result);
            console.dir(this.validation_session_data.new_failures);
        } else {
            console.info(`DOM validation results: No new issues found.`);
        }
    }

    /**
     *
     * @param {Element} element
     * @param {string} failure_reason
     */
    static reportElement(element, failure_reason) {
        if (element === undefined || element === null) {
            return;
        }
        // Globally ignore any reported elements that are children of the debug info panel
        if (element.closest('div.debug-panel')) {
            return;
        }
        if (this.reported_elements.includes(element)) {
            return;
        }

        const message = `${failure_reason}: ${element.tagName} ${element.id} ${element.className}`;
        this.validation_session_data.new_failures.push({
            message: message,
            element: element,
        });
    }
}
// Auto-start DOM validation
GlpiDevDOMValidationTool.start();
