require('bootstrap');
require('../scss/signature.scss');
require('jquery');

// eslint-disable-next-line no-lone-blocks
{
    /** @type {HTMLElement} */
    /** @type {number[]} */ // array types
    /** @type {{ a: string, b: number }} */
    /** @type {function(string, boolean): number} Closure syntax */

    // Parameters may be declared in a variety of syntactic forms
    /**
     * @param {string}  p1 - A string param.
     * @param {string=} p2 - An optional param (Closure syntax)
     * @param {string} [p3] - Another optional param (JSDoc syntax).
     * @param {string} [p4="test"] - An optional param with a default value
     * @return {string} This is the result
     */
    // function stringsStringStrings(p1, p2, p3, p4){ // TODO  }

    /**  @param {string}  p1 - A string param.  */
    /**  @param {Toto}  p1 - A Toto param.  */
}


class CanvasObject {
    constructor() {
        // Paramètres du canvas
        this.canvas = document.getElementById('canvas');
        this.ctx = this.canvas.getContext('2d');
        this.ctx.strokeStyle = '#000000';
        this.ctx.lineWidth = 3;
        this.draw = false;
        this.mousePosition = {
            x: 0,
            y: 0,
        };
        this.lastPosition = this.mousePosition;
        this.clearButton = document.getElementById('bt-clear');
        this.canvas.width = 450;
        this.canvas.height = 250;
    }

    // Events management
    evenements() {
        const self = this;
        // Mouse
        this.canvas.addEventListener('mousedown', (e) => {
            self.draw = true;
            self.lastPosition = self.getMposition(e);
        });

        this.canvas.addEventListener('mousemove', (e) => {
            self.mousePosition = self.getMposition(e);
            self.canvasResult();
        });

        // this.canvas.addEventListener("mouseup", function (e) {
        //     self.draw = false;
        // });
        document.addEventListener('mouseup', (e) => {
            self.draw = false;
        });


        // Stop scrolling (touch)
        document.body.addEventListener('touchstart', (e) => {
            if (e.target === self.canvas) {
                e.preventDefault();
            }
        });

        document.body.addEventListener('touchend', (e) => {
            if (e.target === self.canvas) {
                e.preventDefault();
            }
        });

        document.body.addEventListener('touchmove', (e) => {
            if (e.target === self.canvas) {
                e.preventDefault();
            }
        });


        // Touchpad
        this.canvas.addEventListener('touchstart', (e) => {
            self.mousePosition = self.getTposition(e);
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY,
            });
            self.canvas.dispatchEvent(mouseEvent);
        });

        this.canvas.addEventListener('touchmove', (e) => {
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY,
            });
            self.canvas.dispatchEvent(mouseEvent);
        });

        this.canvas.addEventListener('touchend', (e) => {
            const mouseEvent = new MouseEvent('mouseup', {});
            self.canvas.dispatchEvent(mouseEvent);
        });


        // Erase
        this.clearButton.addEventListener('click', (e) => {
            self.clearCanvas();
        });
    }

    // Give mouse coordinates
    // eslint-disable-next-line consistent-return
    getMposition(mouseEvent) {
        if (this.draw) {
            const oRect = this.canvas.getBoundingClientRect();
            return {
                x: mouseEvent.clientX - oRect.left,
                y: mouseEvent.clientY - oRect.top,
            };
        }
    }

    // Give coordinates of pad
    getTposition(touchEvent) {
        const oRect = this.canvas.getBoundingClientRect();
        return {
            x: touchEvent.touches[0].clientX - oRect.left,
            y: touchEvent.touches[0].clientY - oRect.top,
        };
    }

    // Drawing of canvas
    canvasResult() {
        if (this.draw) {
            this.ctx.beginPath();
            this.ctx.moveTo(this.lastPosition.x, this.lastPosition.y);
            this.ctx.lineTo(this.mousePosition.x, this.mousePosition.y);
            this.ctx.stroke();
            this.lastPosition = this.mousePosition;
        }
    }

    // Clear canvas
    clearCanvas() {
        this.canvas.width = this.canvas.width;
        this.ctx.lineWidth = 3;
    }
}

const obj = new CanvasObject();
obj.evenements();
