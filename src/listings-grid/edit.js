import apiFetch from "@wordpress/api-fetch";

export default (props) => {
    if(!props.attributes.categories) {
        apiFetch({
            url: '/wp-json/wp/v2/categories'
        }).then(categories => {
            props.setAttributes({
                categories: categories
            });
        }).catch(err => console.error(err));
    }

    if(!props.attributes.categories) {
        return 'Loading categories...';
    }

    if(props.attributes.categories && props.attributes.categories.length === 0) {
        return 'No categories';
    }

    function updateCategory(event) {
        props.setAttributes({
            selectedCategory: event.target.value
        })
    }

    const labelStyle = {
        display: 'block'
    }
    const formGroupStyle = {
        border: '1px solid #ccc',
        padding: '1rem'
    }

    return (
        <div>
            <div style={formGroupStyle}>
                <label style={labelStyle}>Select Business Category to Display</label>
                <select onChange={updateCategory} value={props.attributes.selectedCategory}>
                    {
                        props.attributes.categories.map(category => {
                            return (
                                <option value={category.id} key={category.id}>{category.name}</option>
                            )
                        })
                    }
                </select>
            </div>
        </div>
    );
}
