export const STATE_LOADING = 1;
export const STATE_LOADED = 2;

export class States {
    constructor() {
        this.state = {
            value: STATE_LOADING,
            states: {
                STATE_LOADING: STATE_LOADING,
                STATE_LOADED: STATE_LOADED
            }
        }
    }

    getState(){
        return this.state;
    }
}