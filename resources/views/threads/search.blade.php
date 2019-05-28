@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <ais-instant-search :search-client="searchClient" index-name="threads" :routing="routing">
            <div class="col-md-8">
                <ais-hits>
                    <ul slot-scope="{ items }">
                        <li v-for="item in items" :key="item.objectID">
                            <a :href="item.path">
                                <ais-highlight :hit="item" attribute="title" />
                            </a>
                        </li>            
                    </ul>
                </ais-hits>
                
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Search
                    </div>
                    <div class="panel-body">
                        <ais-search-box>
                            <div slot-scope="{ currentRefinement, isSearchStalled, refine }">
                                <input type="search"
                                       v-model="currentRefinement"
                                       @input="refine($event.currentTarget.value)"
                                       class="form-control"
                                       placeholder="Find a thread..."
                                       :autofocus="true"
                                >
                                <span :hidden="!isSearchStalled">Loading...</span>
                            </div>
                        </ais-search-box>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Filter by Channel
                    </div>
                    <div class="panel-body">
                        <ais-refinement-list attribute="channel.name">
                            <div slot-scope="{items, refine, createURL}">
                                <div v-for="item in items" :key="item.value">
                                    <div class="checkbox cb-no-mr">
                                        <label :style="{ fontWeight: item.isRefined ? 'bold' : '' }">
                                            <input
                                                type="checkbox"
                                                :href="createURL(item.value)"              
                                                @click="refine(item.value)"
                                                :checked="item.isRefined"
                                                >
                                            <span v-text="item.label + ' (' + item.count + ')'"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </ais-refinement-list>
                    </div>
                </div>


                @if (count($trending))
                	<div class="panel panel-default">
                		<div class="panel-heading">
                			Trending Threads
                		</div>
                		<div class="panel-body">
                			<ul class="list-group">
        	        			@foreach ($trending as $thread)
        	        				<li class="list-group-item">
        	        					<a href="{{ $thread->path }}">{{ $thread->title }}</a>
        	        				</li>
        	        			@endforeach
                			</ul>
                		</div>
                	</div>
                @endif
            </div>
        </ais-instant-search>
    </div>
</div>
@endsection