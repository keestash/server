export const STORE = {
    state: {
        demo: {
            emailSubmitted: false
        }
    },
    mutations: {
        SET_EMAIL_SUBMITTED(state, emailSubmitted) {
            state.demo.emailSubmitted = emailSubmitted;
        }
    },
    actions: {
        setEmailSubmitted(context, emailSubmitted) {
            context.commit("SET_EMAIL_SUBMITTED", emailSubmitted);
        },
    },
    getters: {
        emailSubmitted(state) {
            return state.demo.emailSubmitted
        }
    }
}