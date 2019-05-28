<template>
<ais-instant-search :search-client="searchClient" index-name="threads">
    <ais-search-box></ais-search-box>
    <ais-refinement-list attribute="channel.name"/>
    <ais-hits>
        <template slot-scope="{ items }">
            <p v-for="item in items" :key="item.objectID">
                <a :href="item.path">
                    <ais-highlight :hit="item" attribute="title" />
                </a>
            </p>            
        </template>
    </ais-hits>
</ais-instant-search>
</template>

<script>
import algoliasearch from 'algoliasearch/lite';

export default {
    data() {
        return {
            searchClient: algoliasearch(
                process.env.MIX_ALGOLIA_APP_ID,
                process.env.MIX_ALGOLIA_SEARCH
            ),
        };
    },
};
</script>