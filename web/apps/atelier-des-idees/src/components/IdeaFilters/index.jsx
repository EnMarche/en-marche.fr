import React from 'react';
import PropTypes from 'prop-types';
import { ideaStatus, AUTHOR_CATEGORIES } from '../../constants/api';
import Select from '../Select';
import searchIcn from '../../img/icn_input_search.svg';

class IdeaFilters extends React.Component {
    constructor(props) {
        super(props);
        this.filterItems = {
            order: {
                options: [
                    { value: 'order[publishedAt]/DESC', label: 'Plus récentes' },
                    { value: 'order[publishedAt]/ASC', label: 'Plus anciennes' },
                    { value: 'commentsCount/DESC', label: 'Plus commentées' },
                    {
                        value: 'order[votesCount]/DESC',
                        label: 'Plus votées',
                        status: ideaStatus.FINALIZED,
                    },
                ],
            },
            authorCategory: {
                options: Object.entries(AUTHOR_CATEGORIES).map(([key, value]) => ({
                    label: value,
                    value: key,
                })),
            },
        };
        this.state = {
            name: props.defaultValues.name || '',
            authorCategory: null,
            'themes.name': '',
            'category.name': '',
            'needs.name': '',
            order: this.filterItems.order.options[0].value,
        };
        // bindings
        this.onFilterChange = this.onFilterChange.bind(this);
        this.formatFilters = this.formatFilters.bind(this);
        this.getDefaultValue = this.getDefaultValue.bind(this);
    }

    onFilterChange(filterKey, value) {
        this.setState({ [filterKey]: value }, () => this.props.onFilterChange(this.formatFilters()));
    }

    formatFilters() {
        const { name, ...filters } = this.state;
        const formattedFilters = Object.entries(filters).reduce((acc, [filterName, filterValue]) => {
            if (filterValue) {
                const [attr, value] = filterValue.split('/');
                if (value) {
                    acc[attr] = value;
                    return acc;
                }
                acc[filterName] = filterValue;
            }
            return acc;
        }, {});
        return name ? { name, ...formattedFilters } : formattedFilters;
    }

    getDefaultValue(options, key) {
        return options.find(item => item.value === this.props.defaultValues[key]);
    }

    render() {
        return (
            <div className="idea-filters">
                <div className="idea-filters__section idea-filters__filter">
                    <p className="idea-filters__label">Filtrer par</p>
                    <div className="idea-filters__section__filters">
                        <span className="idea-filters__input-wrapper">
                            <img src={searchIcn} className="idea-filters__input-icon" />
                            <input
                                className="idea-filters__input"
                                value={this.state.name}
                                onChange={e => this.onFilterChange('name', e.target.value)}
                                placeholder="Mot clé"
                            />
                        </span>
                        {!!this.props.options && (
                            <React.Fragment>
                                {!!this.props.options.categories.length && (
                                    <Select
                                        options={this.props.options.categories}
                                        placeholder="National / Européen"
                                        defaultValue={this.getDefaultValue(
                                            this.props.options.categories,
                                            'category.name'
                                        )}
                                        onSelected={([selected]) =>
                                            this.onFilterChange('category.name', selected && selected.value)
                                        }
                                        isClearable={true}
                                        isDisabled={this.props.disabled}
                                    />
                                )}
                                {!!this.props.options.themes.length && (
                                    <Select
                                        options={this.props.options.themes}
                                        defaultValue={this.getDefaultValue(this.props.options.themes, 'themes.name')}
                                        placeholder="Thème"
                                        onSelected={([selected]) =>
                                            this.onFilterChange('themes.name', selected && selected.value)
                                        }
                                        isClearable={true}
                                        isDisabled={this.props.disabled}
                                    />
                                )}
                            </React.Fragment>
                        )}
                        <Select
                            options={this.filterItems.authorCategory.options}
                            placeholder="Type de contributeur"
                            defaultValue={this.getDefaultValue(
                                this.filterItems.authorCategory.options,
                                'authorCategory'
                            )}
                            onSelected={([selected]) =>
                                this.onFilterChange('authorCategory', selected && selected.value)
                            }
                            isClearable={true}
                            isDisabled={this.props.disabled}
                        />
                        {this.props.status === ideaStatus.PENDING &&
                            !!this.props.options &&
                            !!this.props.options.needs.length && (
                                <Select
                                    options={this.props.options.needs}
                                    defaultValue={this.getDefaultValue(this.props.options.needs, 'needs.name')}
                                    placeholder="Besoin"
                                    onSelected={([selected]) =>
                                        this.onFilterChange('needs.name', selected && selected.value)
                                    }
                                    isClearable={true}
                                    isDisabled={this.props.disabled}
                                />
                            )}
                    </div>
                </div>
                <div className="idea-filters__section idea-filters__sort">
                    <p className="idea-filters__label">Trier par</p>
                    <div className="idea-filters__section__filters">
                        <Select
                            options={this.filterItems.order.options.filter(
                                option => !option.status || (!!option.status && option.status === this.props.status)
                            )}
                            defaultValue={
                                this.getDefaultValue(this.filterItems.order.options, 'order') ||
                                this.filterItems.order.options[0]
                            }
                            onSelected={([selected]) => this.onFilterChange('order', selected.value)}
                            isDisabled={this.props.disabled}
                        />
                    </div>
                </div>
            </div>
        );
    }
}

IdeaFilters.defaultProps = {
    defaultValues: {},
    disabled: false,
    status: 'PENDING',
    options: undefined,
};

IdeaFilters.propTypes = {
    onFilterChange: PropTypes.func.isRequired,
    status: PropTypes.oneOf(Object.keys(ideaStatus)),
    options: PropTypes.shape({
        themes: PropTypes.array,
        categories: PropTypes.array,
        needs: PropTypes.array,
    }),
    defaultValues: PropTypes.shape({
        authorCategory: PropTypes.string,
        'category.name': PropTypes.string,
        name: PropTypes.string,
        'needs.name': PropTypes.string,
        order: PropTypes.string,
        'themes.name': PropTypes.string,
    }),
    disabled: PropTypes.bool,
};

export default IdeaFilters;
