<?php
/**
 * Template for search results (Handlebars)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Template for search results -->
<script id="results-template" type="text/x-handlebars-template">
    <div class="search-results-count">{{total_results}} awards found</div>
    <div class="search-results-list">
        {{#each results}}
            <div class="award-item">
                <h3>{{this.[Award Name]}}</h3>
                
                <div class="award-details">
                    <!-- Award Number -->
                    <p><strong>Award Number:</strong> {{this.[Award Number]}}</p>
                    
                    <!-- Award Cycle -->
                    <p><strong>Award Cycle:</strong> {{this.[Award Cycle]}}</p>
                    
                    <!-- Award Type -->
                    {{#if this.[Award Type]}}
                        <p><strong>Award Type:</strong> 
                            {{#ifeq this.[Award Type] "AWRD"}}Award{{/ifeq}}
                            {{#ifeq this.[Award Type] "SCHL"}}Scholarship{{/ifeq}}
                            {{#ifeq this.[Award Type] "PRIZ"}}Prize{{/ifeq}}
                            {{#ifeq this.[Award Type] "BURS"}}Bursaries{{/ifeq}}
                            {{#ifeq this.[Award Type] "FELL"}}Fellowship{{/ifeq}}
                            {{#ifneq this.[Award Type] "AWRD"}}
                                {{#ifneq this.[Award Type] "SCHL"}}
                                    {{#ifneq this.[Award Type] "PRIZ"}}
                                        {{#ifneq this.[Award Type] "BURS"}}
                                            {{#ifneq this.[Award Type] "FELL"}}
                                                {{this.[Award Type]}}
                                            {{/ifneq}}
                                        {{/ifneq}}
                                    {{/ifneq}}
                                {{/ifneq}}
                            {{/ifneq}}
                        </p>
                    {{/if}}
                    
                    <!-- Department -->
                    {{#if this.[Administering Unit]}}
                        <p><strong>Department:</strong> {{formatAdminUnit this.[Administering Unit]}}</p>
                    {{/if}}
                    
                    <!-- Degree Level -->
                    <p><strong>Degree Level:</strong> {{this.[Eligible Learner Level]}}</p>
                    
                    <!-- Application Type -->
                    <p><strong>Application Type:</strong> {{this.[Application Type (Award Profile)]}}</p>
                    
                    <!-- Campus -->
                    {{#if this.Campus}}
                        <p><strong>Campus:</strong>
                            {{#ifeq this.Campus "V"}}Vancouver{{/ifeq}}
                            {{#ifeq this.Campus "O"}}Okanagan{{/ifeq}}
                            {{#ifeq this.Campus "A"}}All Campuses{{/ifeq}}
                            {{#ifneq this.Campus "V"}}
                                {{#ifneq this.Campus "O"}}
                                    {{#ifneq this.Campus "A"}}
                                        {{this.Campus}}
                                    {{/ifneq}}
                                {{/ifneq}}
                            {{/ifneq}}
                        </p>
                    {{/if}}
                    
                    <!-- Award Description -->
                    {{#if this.[Award Description]}}
                        <div class="award-description">
                            <h4>Description</h4>
                            <p>{{this.[Award Description]}}</p>
                        </div>
                    {{/if}}
                </div>
            </div>
        {{/each}}
    </div>
</script>