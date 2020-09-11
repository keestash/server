export class ArrayListHelper {
    excludeNullValues(data) {
        const sanitized = [];
        data.forEach(
            (v, i) => {
                if (v !== null) {
                    sanitized[i] = v;
                }
            }
        )
        return sanitized;
    }
}
